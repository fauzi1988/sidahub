<?php

namespace App\Http\Controllers;

use App\Models\Operasional;
use App\Models\OperasionalItem;
use App\Models\OperasionalPetugasPeriode;
use App\Models\PenyerahanKarcis;
use App\Models\PetugasKarcis;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OperasionalController extends Controller
{
    public function index(Request $request): View
    {
        $tahun = $this->resolveTahun($request);
        $tanggalLaporan = $this->resolveTanggalLaporan($request);
        $tahunBerjalan = (int) date('Y');
        $tahunAwal = 2026;
        $tahunAkhir = max(2028, $tahunBerjalan + 1);
        $tahunOptions = range($tahunAkhir, $tahunAwal);

        $operasionalGrouped = Operasional::query()
            ->where('tahun', $tahun)
            ->orderBy('id')
            ->get(['id', 'tahun', 'nama_penanggungjawab', 'tempat_tugas'])
            ->mapWithKeys(fn ($row) => [
                $this->buildGroupKey($row->tempat_tugas, (int) $row->tahun) => $row,
            ]);

        $filteredItemsQuery = OperasionalItem::query()
            ->whereNotNull('source_penyerahan_karcis_id')
            ->whereHas('operasional', function ($q) use ($tahun) {
                $q->where('tahun', $tahun)
                    ->when($this->shouldRestrictPetugasAccess(), fn ($sub) => $sub->whereHas('penyerahan', fn ($p) => $p->where('pihak_kedua_id_pegawai', $this->currentPegawaiId())));
            })
            ->when($tanggalLaporan, fn ($q) => $q->whereDate('tanggal_laporan', $tanggalLaporan));

        $filteredPenyerahanIds = (clone $filteredItemsQuery)
            ->pluck('source_penyerahan_karcis_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $totalLembarTerjual = (int) (clone $filteredItemsQuery)->sum('lembar_terjual');
        $totalTransaksiItem = (int) (clone $filteredItemsQuery)->count();
        $totalNominalPenjualan = (float) (clone $filteredItemsQuery)->sum('total_terjual');

        $list = PenyerahanKarcis::query()
            ->whereYear('tanggal', $tahun)
            ->when($this->shouldRestrictPetugasAccess(), fn ($q) => $q->where('pihak_kedua_id_pegawai', $this->currentPegawaiId()))
            ->when($tanggalLaporan, function ($q) use ($filteredPenyerahanIds) {
                $ids = $filteredPenyerahanIds->all();
                $q->whereIn('id', ! empty($ids) ? $ids : [0]);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $totalBastDitemukan = (int) $filteredPenyerahanIds->count();

        return view('admin.Retribusi.operasional.index', compact(
            'list',
            'tahun',
            'tahunOptions',
            'operasionalGrouped',
            'tanggalLaporan',
            'totalBastDitemukan',
            'totalLembarTerjual',
            'totalTransaksiItem',
            'totalNominalPenjualan'
        ));
    }

    public function create(Request $request): View
    {
        $penyerahanId = (int) $request->query('penyerahan');
        $tahun = $this->resolveTahun($request);
        abort_if($penyerahanId <= 0, 404);

        $penyerahan = PenyerahanKarcis::query()
            ->with('items.karcis')
            ->findOrFail($penyerahanId);
        $this->ensurePenyerahanAccessibleByCurrentUser($penyerahan);

        // Pastikan satu paket: data BAST harus sesuai tahun yang dipilih.
        abort_if((int) optional($penyerahan->tanggal)->format('Y') !== $tahun, 404);

        $operasionalTahun = Operasional::query()
            ->where('tahun', $tahun)
            ->where('tempat_tugas', $penyerahan->pihak_kedua_tempat_tugas)
            ->with('items', 'petugasPeriode')
            ->first();

        $petugasOptions = PetugasKarcis::query()
            ->orderBy('nama_pegawai')
            ->get(['nama_pegawai', 'tempat_tugas']);

        $tanggalLaporanSudahAda = OperasionalItem::query()
            ->where('source_penyerahan_karcis_id', $penyerahan->id)
            ->whereNotNull('tanggal_laporan')
            ->orderByDesc('tanggal_laporan')
            ->pluck('tanggal_laporan')
            ->map(fn ($tgl) => Carbon::parse((string) $tgl)->format('Y-m-d'))
            ->unique()
            ->values();

        return view('admin.Retribusi.operasional.create', compact(
            'penyerahan',
            'tahun',
            'operasionalTahun',
            'petugasOptions',
            'tanggalLaporanSudahAda'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->storePerKarcisRules());
        $submittedItems = collect($data['items'] ?? []);

        foreach ($submittedItems as $idx => $item) {
            $lembarTerjual = (int) ($item['lembar_terjual'] ?? 0);
            $sisaSebelumnya = (int) ($item['sisa_sebelumnya'] ?? 0);
            if ($lembarTerjual > $sisaSebelumnya) {
                return back()
                    ->withInput()
                    ->withErrors(["items.{$idx}.lembar_terjual" => 'Lembar terjual tidak boleh melebihi sisa lembar.']);
            }
        }

        $validItems = $submittedItems
            ->filter(function (array $item): bool {
                $lembarTerjual = (int) ($item['lembar_terjual'] ?? 0);
                $hasBukti = ! empty($item['bukti_setor'] ?? null);

                return $lembarTerjual > 0 || $hasBukti;
            });

        if ($validItems->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'Isi minimal satu data karcis terjual atau upload bukti setor.']);
        }

        $sudahAdaLaporanDiTanggalIni = OperasionalItem::query()
            ->where('source_penyerahan_karcis_id', (int) $data['penyerahan_karcis_id'])
            ->whereDate('tanggal_laporan', $data['tanggal_laporan'])
            ->exists();

        if ($sudahAdaLaporanDiTanggalIni) {
            return back()
                ->withInput()
                ->withErrors([
                    'tanggal_laporan' => 'Sudah melakukan pelaporan di tanggal ini. Silakan edit/hapus laporan yang ada pada menu detail.',
                ]);
        }

        DB::transaction(function () use ($request, $data, $validItems): void {
            $operasional = Operasional::firstOrCreate(
                [
                    'tahun' => (int) $data['tahun'],
                    'tempat_tugas' => $data['tempat_tugas'] ?? null,
                ],
                [
                    'penyerahan_karcis_id' => (int) $data['penyerahan_karcis_id'],
                    'tanggal' => $data['tanggal'],
                    'tahun' => (int) $data['tahun'],
                    'nomor_bast' => $data['nomor_bast'],
                    'nama_penanggungjawab' => $data['nama_penanggungjawab'],
                    'tempat_tugas' => $data['tempat_tugas'] ?? null,
                    'bukti_setor' => null,
                ]
            );

            $operasional->update([
                'tahun' => (int) $data['tahun'],
                'nama_penanggungjawab' => $data['nama_petugas'],
                'tempat_tugas' => $data['tempat_tugas'] ?? null,
            ]);

            $this->syncPetugasPeriode($operasional, $data['nama_petugas'], Carbon::now()->toDateString());

            foreach ($validItems as $index => $item) {
                $lembar = (int) $item['lembar'];
                $harga = (float) $item['harga_satuan'];
                $sisaSebelumnya = (int) $item['sisa_sebelumnya'];
                $lembarTerjual = min((int) $item['lembar_terjual'], $sisaSebelumnya);

                $newBuktiSetor = null;
                if ($request->hasFile("items.{$index}.bukti_setor")) {
                    $newBuktiSetor = $request->file("items.{$index}.bukti_setor")->store('operasional-bukti-setor', 'public');
                }

                $operasional->items()->create([
                    'source_penyerahan_karcis_id' => (int) $data['penyerahan_karcis_id'],
                    'source_nomor_bast' => $data['nomor_bast'],
                    'tanggal_laporan' => $data['tanggal_laporan'],
                    'nama_petugas' => $data['nama_petugas'],
                    'karcis_kode' => $item['karcis_kode'],
                    'nama_karcis' => $item['nama_karcis'],
                    'harga_satuan' => $harga,
                    'lembar' => $lembar,
                    'total' => $harga * $lembar,
                    'lembar_terjual' => $lembarTerjual,
                    'total_terjual' => $harga * $lembarTerjual,
                    'total_penjualan' => 0,
                    'sisa_lembar' => max(0, $sisaSebelumnya - $lembarTerjual),
                    'bukti_setor' => $newBuktiSetor,
                ]);

                $this->recalculateTotalPenjualan($operasional, (string) $item['karcis_kode']);
            }
        });

        return redirect()
            ->route('operasional.create', [
                'penyerahan' => $data['penyerahan_karcis_id'],
                'tahun' => $data['tahun'],
            ])
            ->with('success', 'Laporan operasional harian berhasil disimpan.');
    }

    public function show(Operasional $operasional): View
    {
        $operasional->load([
            'penyerahan:id,nomor_bast,tanggal,pihak_kedua_nama,pihak_kedua_tempat_tugas',
            'petugasPeriode' => fn ($q) => $q->latest('tanggal_mulai'),
            'items' => fn ($q) => $q->latest('created_at'),
        ]);
        $this->ensureOperasionalAccessibleByCurrentUser($operasional);

        return view('admin.Retribusi.operasional.show', compact('operasional'));
    }

    public function edit(Operasional $operasional): View
    {
        $operasional->load('items', 'penyerahan:id,nomor_bast,tanggal,pihak_kedua_nama');
        $this->ensureOperasionalAccessibleByCurrentUser($operasional);

        return view('admin.Retribusi.operasional.edit', compact('operasional'));
    }

    public function editItem(OperasionalItem $operasional_item): View
    {
        $operasional_item->load('operasional.penyerahan');
        $this->ensureOperasionalAccessibleByCurrentUser($operasional_item->operasional);

        return view('admin.Retribusi.operasional.edit_item', ['item' => $operasional_item]);
    }

    public function updateItem(Request $request, OperasionalItem $operasional_item): RedirectResponse
    {
        $operasional_item->loadMissing('operasional.penyerahan');
        $this->ensureOperasionalAccessibleByCurrentUser($operasional_item->operasional);

        $data = $request->validate([
            'lembar_terjual' => ['required', 'integer', 'min:0'],
            'bukti_setor' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $sisaSebelumnya = (int) $operasional_item->sisa_lembar + (int) $operasional_item->lembar_terjual;
        $lembarTerjualBaru = min((int) $data['lembar_terjual'], $sisaSebelumnya);
        $harga = (float) $operasional_item->harga_satuan;

        $payload = [
            'lembar_terjual' => $lembarTerjualBaru,
            'total_terjual' => $harga * $lembarTerjualBaru,
            'total_penjualan' => 0,
            'sisa_lembar' => max(0, $sisaSebelumnya - $lembarTerjualBaru),
        ];

        if ($request->hasFile('bukti_setor')) {
            if ($operasional_item->bukti_setor) {
                Storage::disk('public')->delete($operasional_item->bukti_setor);
            }
            $payload['bukti_setor'] = $request->file('bukti_setor')->store('operasional-bukti-setor', 'public');
        }

        $operasional_item->update($payload);
        $this->recalculateTotalPenjualan($operasional_item->operasional, (string) $operasional_item->karcis_kode);

        return redirect()
            ->route('operasional.show', $operasional_item->operasional_id)
            ->with('success', 'Data operasional per karcis berhasil diperbarui.');
    }

    public function destroyItem(OperasionalItem $operasional_item): RedirectResponse
    {
        $operasional_item->loadMissing('operasional.penyerahan');
        $this->ensureOperasionalAccessibleByCurrentUser($operasional_item->operasional);

        $operasionalId = $operasional_item->operasional_id;
        $karcisKode = (string) $operasional_item->karcis_kode;

        if ($operasional_item->bukti_setor) {
            Storage::disk('public')->delete($operasional_item->bukti_setor);
        }

        $operasional_item->delete();

        $operasional = Operasional::find($operasionalId);
        if ($operasional) {
            $this->recalculateTotalPenjualan($operasional, $karcisKode);
        }

        return redirect()
            ->route('operasional.show', $operasionalId)
            ->with('success', 'Data operasional karcis berhasil dihapus.');
    }

    public function update(Request $request, Operasional $operasional): RedirectResponse
    {
        $operasional->loadMissing('penyerahan');
        $this->ensureOperasionalAccessibleByCurrentUser($operasional);

        $data = $request->validate($this->rules($operasional->id));

        DB::transaction(function () use ($request, $data, $operasional): void {
            $payload = [
                'tanggal' => $data['tanggal'],
                'tahun' => (int) $data['tahun'],
                'nomor_bast' => $data['nomor_bast'],
                'nama_penanggungjawab' => $data['nama_penanggungjawab'],
                'tempat_tugas' => $data['tempat_tugas'] ?? null,
            ];

            if ($request->hasFile('bukti_setor')) {
                if ($operasional->bukti_setor) {
                    Storage::disk('public')->delete($operasional->bukti_setor);
                }
                $payload['bukti_setor'] = $request->file('bukti_setor')->store('operasional-bukti-setor', 'public');
            }

            $operasional->update($payload);
            $operasional->items()->delete();

            foreach ($data['items'] as $item) {
                $lembar = (int) $item['lembar'];
                $harga = (float) $item['harga_satuan'];
                $lembarTerjual = min((int) $item['lembar_terjual'], $lembar);

                $operasional->items()->create([
                    'karcis_kode' => $item['karcis_kode'] ?: null,
                    'nama_karcis' => $item['nama_karcis'],
                    'harga_satuan' => $harga,
                    'lembar' => $lembar,
                    'total' => $harga * $lembar,
                    'lembar_terjual' => $lembarTerjual,
                    'total_terjual' => $harga * $lembarTerjual,
                    'sisa_lembar' => max(0, $lembar - $lembarTerjual),
                ]);
            }
        });

        return redirect()
            ->route('operasional.index')
            ->with('success', 'Laporan operasional berhasil diperbarui.');
    }

    public function destroy(Operasional $operasional): RedirectResponse
    {
        $operasional->loadMissing('penyerahan');
        $this->ensureOperasionalAccessibleByCurrentUser($operasional);

        foreach ($operasional->items as $item) {
            if ($item->bukti_setor) {
                Storage::disk('public')->delete($item->bukti_setor);
            }
        }
        if ($operasional->bukti_setor) {
            Storage::disk('public')->delete($operasional->bukti_setor);
        }
        $operasional->delete();

        return redirect()
            ->route('operasional.index')
            ->with('success', 'Laporan operasional berhasil dihapus.');
    }

    private function rules(?int $operasionalId = null): array
    {
        return [
            'penyerahan_karcis_id' => [
                'required',
                'exists:penyerahan_karcis,id',
                Rule::unique('operasional')
                    ->where(fn ($q) => $q
                        ->where('tahun', (int) request('tahun'))
                        ->where('tempat_tugas', request('tempat_tugas')))
                    ->ignore($operasionalId),
            ],
            'tanggal' => ['required', 'date'],
            'tanggal_laporan' => ['required', 'date'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'nomor_bast' => ['required', 'string', 'max:120'],
            'nama_penanggungjawab' => ['nullable', 'string', 'max:150'],
            'nama_petugas' => ['required', 'string', 'max:150'],
            'tempat_tugas' => ['nullable', 'string', 'max:150'],
            'bukti_setor' => [$operasionalId === null ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.karcis_kode' => ['nullable', 'string', 'max:30'],
            'items.*.nama_karcis' => ['required', 'string', 'max:255'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
            'items.*.lembar' => ['required', 'integer', 'min:0'],
            'items.*.lembar_terjual' => ['required', 'integer', 'min:0'],
        ];
    }

    private function storePerKarcisRules(): array
    {
        return [
            'penyerahan_karcis_id' => ['required', 'exists:penyerahan_karcis,id'],
            'tanggal' => ['required', 'date'],
            'tanggal_laporan' => ['required', 'date'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'nomor_bast' => ['required', 'string', 'max:120'],
            'nama_penanggungjawab' => ['required', 'string', 'max:150'],
            'nama_petugas' => ['required', 'string', 'max:150'],
            'tempat_tugas' => ['nullable', 'string', 'max:150'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.karcis_kode' => ['required', 'string', 'max:30'],
            'items.*.nama_karcis' => ['required', 'string', 'max:255'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
            'items.*.lembar' => ['required', 'integer', 'min:0'],
            'items.*.sisa_sebelumnya' => ['required', 'integer', 'min:0'],
            'items.*.lembar_terjual' => ['required', 'integer', 'min:0'],
            'items.*.bukti_setor' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    private function recalculateTotalPenjualan(Operasional $operasional, string $karcisKode): void
    {
        $running = 0.0;

        $rows = $operasional->items()
            ->where('karcis_kode', $karcisKode)
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $running += (float) $row->total_terjual;
            $row->update(['total_penjualan' => $running]);
        }
    }

    private function resolveTahun(Request $request): int
    {
        $tahunBerjalan = (int) date('Y');
        $tahun = (int) $request->query('tahun', $tahunBerjalan);

        if ($tahun < 2000 || $tahun > 2100) {
            return $tahunBerjalan;
        }

        return $tahun;
    }

    private function buildGroupKey(?string $tempatTugas, int $tahun): string
    {
        return mb_strtolower(trim((string) $tempatTugas)).'|'.$tahun;
    }

    private function resolveTanggalLaporan(Request $request): ?string
    {
        $tanggal = trim((string) $request->query('tanggal_laporan', ''));
        if ($tanggal === '') {
            return null;
        }

        try {
            return Carbon::parse($tanggal)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function syncPetugasPeriode(Operasional $operasional, string $namaPetugas, string $tanggal): void
    {
        $aktif = $operasional->petugasPeriode()
            ->whereNull('tanggal_selesai')
            ->latest('tanggal_mulai')
            ->first();

        if ($aktif && $aktif->nama_petugas === $namaPetugas) {
            return;
        }

        if ($aktif) {
            $aktif->update(['tanggal_selesai' => $tanggal]);
        }

        OperasionalPetugasPeriode::create([
            'operasional_id' => $operasional->id,
            'nama_petugas' => $namaPetugas,
            'tanggal_mulai' => $tanggal,
            'tanggal_selesai' => null,
        ]);
    }

    private function shouldRestrictPetugasAccess(): bool
    {
        $user = Auth::user();

        return (bool) ($user && ! $user->is_super_admin && ! empty($user->id_pegawai));
    }

    private function currentPegawaiId(): ?int
    {
        return Auth::user()?->id_pegawai ? (int) Auth::user()->id_pegawai : null;
    }

    private function ensurePenyerahanAccessibleByCurrentUser(PenyerahanKarcis $penyerahan): void
    {
        if (! $this->shouldRestrictPetugasAccess()) {
            return;
        }

        abort_if((int) $penyerahan->pihak_kedua_id_pegawai !== (int) $this->currentPegawaiId(), 403);
    }

    private function ensureOperasionalAccessibleByCurrentUser(Operasional $operasional): void
    {
        if (! $this->shouldRestrictPetugasAccess()) {
            return;
        }

        $operasional->loadMissing('penyerahan:id,pihak_kedua_id_pegawai');
        abort_if((int) optional($operasional->penyerahan)->pihak_kedua_id_pegawai !== (int) $this->currentPegawaiId(), 403);
    }
}
