<?php

namespace App\Http\Controllers;

use App\Models\Karcis;
use App\Models\PenerimaanKarcis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PenerimaanKarcisController extends Controller
{
    public function index(Request $request): View
    {
        $filterBy = $request->query('filter_by', 'nomor_bast');
        $keyword = trim((string) $request->query('keyword', ''));

        $query = PenerimaanKarcis::query()
            ->with('karcis:kode_karcis,nama_karcis');

        if ($keyword !== '') {
            if ($filterBy === 'nama_karcis') {
                $query->whereHas('karcis', function ($q) use ($keyword): void {
                    $q->where('nama_karcis', 'like', '%'.$keyword.'%');
                });
            } elseif ($filterBy === 'tahun') {
                $year = preg_replace('/\D/', '', $keyword);
                if ($year !== '' && strlen($year) === 4) {
                    $query->whereYear('created_at', (int) $year);
                }
            } else {
                $filterBy = 'nomor_bast';
                $query->where('nomor_bast', 'like', '%'.$keyword.'%');
            }
        }

        $list = $query
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.Retribusi.terima_karcis.index', compact('list', 'filterBy', 'keyword'));
    }

    public function create(): View
    {
        $karcisOptions = Karcis::query()
            ->orderBy('nama_karcis')
            ->get(['kode_karcis', 'nama_karcis', 'harga_satuan']);

        return view('admin.Retribusi.terima_karcis.create', compact('karcisOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->storeRules());
        $filePath = $request->hasFile('file_bast') ? $request->file('file_bast')->store('bast', 'public') : null;
        $touchedKarcis = [];

        foreach ($data['items'] as $item) {
            $karcis = Karcis::query()->findOrFail($item['karcis_kode']);

            PenerimaanKarcis::create([
                'nomor_bast' => $data['nomor_bast'],
                'karcis_kode' => $karcis->kode_karcis,
                'harga_satuan' => (float) $karcis->harga_satuan,
                'stock_masuk' => (int) $item['stock_masuk'],
                'total_stock' => 0,
                'total_harga' => 0,
                'file_bast' => $filePath,
            ]);

            $touchedKarcis[$karcis->kode_karcis] = true;
        }

        foreach (array_keys($touchedKarcis) as $karcisKode) {
            $this->recalculateFrom($karcisKode);
        }

        return redirect()
            ->route('terima-karcis.index')
            ->with('success', 'Data penerimaan karcis berhasil ditambahkan.');
    }

    public function show(PenerimaanKarcis $terima_karci): View
    {
        $terima_karci->load('karcis:kode_karcis,nama_karcis');

        return view('admin.Retribusi.terima_karcis.show', ['penerimaan' => $terima_karci]);
    }

    public function edit(PenerimaanKarcis $terima_karci): View
    {
        $terima_karci->load('karcis:kode_karcis,nama_karcis,harga_satuan');

        return view('admin.Retribusi.terima_karcis.edit', ['penerimaan' => $terima_karci]);
    }

    public function update(Request $request, PenerimaanKarcis $terima_karci): RedirectResponse
    {
        $data = $request->validate($this->updateRules());
        $oldKarcisKode = $terima_karci->karcis_kode;
        $karcis = Karcis::query()->findOrFail($data['karcis_kode']);

        $terima_karci->update([
            'nomor_bast' => $data['nomor_bast'],
            'karcis_kode' => $karcis->kode_karcis,
            'harga_satuan' => (float) $karcis->harga_satuan,
            'stock_masuk' => (int) $data['stock_masuk'],
        ]);

        if ($request->hasFile('file_bast')) {
            if ($terima_karci->file_bast) {
                Storage::disk('public')->delete($terima_karci->file_bast);
            }

            $terima_karci->update([
                'file_bast' => $request->file('file_bast')->store('bast', 'public'),
            ]);
        }
        $this->recalculateFrom($karcis->kode_karcis);
        if ($oldKarcisKode !== $karcis->kode_karcis) {
            $this->recalculateFrom($oldKarcisKode);
        }

        return redirect()
            ->route('terima-karcis.index')
            ->with('success', 'Data penerimaan karcis berhasil diperbarui.');
    }

    public function destroy(PenerimaanKarcis $terima_karci): RedirectResponse
    {
        if ($terima_karci->file_bast) {
            Storage::disk('public')->delete($terima_karci->file_bast);
        }

        $terima_karci->delete();
        $this->recalculateFrom($terima_karci->karcis_kode);

        return redirect()
            ->route('terima-karcis.index')
            ->with('success', 'Data penerimaan karcis berhasil dihapus.');
    }

    private function storeRules(): array
    {
        return [
            'nomor_bast' => ['required', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.karcis_kode' => ['required', 'exists:karcis,kode_karcis'],
            'items.*.stock_masuk' => ['required', 'integer', 'min:1'],
            'file_bast' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    private function updateRules(): array
    {
        return [
            'nomor_bast' => ['required', 'string', 'max:100'],
            'karcis_kode' => ['required', 'exists:karcis,kode_karcis'],
            'stock_masuk' => ['required', 'integer', 'min:1'],
            'file_bast' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    private function recalculateFrom(string $karcisKode): void
    {
        $runningStock = 0;
        $runningTotalHarga = 0.0;

        $rows = PenerimaanKarcis::query()
            ->where('karcis_kode', $karcisKode)
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $runningStock += (int) $row->stock_masuk;
            $runningTotalHarga += ((int) $row->stock_masuk * (float) $row->harga_satuan);

            $row->update([
                'total_stock' => $runningStock,
                'total_harga' => $runningTotalHarga,
            ]);
        }
    }
}
