<?php

namespace App\Http\Controllers;

use App\Models\LaporanMingguan;
use App\Models\Operasional;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LaporanMingguanController extends Controller
{
    public function index(Request $request): View
    {
        $tahun = $this->resolveTahun($request);
        $tahunOptions = $this->tahunOptions();
        $bulan = $this->resolveBulan($request);

        $query = LaporanMingguan::query()
            ->where('tahun', $tahun)
            ->when($this->shouldRestrictPetugasAccess(), fn ($q) => $q->whereIn('nama_petugas', $this->currentPetugasNames()))
            ->when($bulan > 0, fn ($q) => $q->whereMonth('tanggal', $bulan));

        $list = $query
            ->orderBy('tanggal')
            ->orderBy('minggu_ke')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.Retribusi.laporan_mingguan.index', compact('list', 'tahun', 'tahunOptions', 'bulan'));
    }

    public function create(Request $request): View
    {
        $tahun = $this->resolveTahun($request);
        [$petugasOptions, $sourceItems] = $this->buildSourceFromOperasional($tahun);

        return view('admin.Retribusi.laporan_mingguan.create', [
            'petugasOptions' => $petugasOptions,
            'sourceItems' => $sourceItems,
            'tahun' => $tahun,
            'tidakAdaDataBast' => $sourceItems->isEmpty(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $this->ensureLaporanPetugasAllowed((string) $data['nama_petugas']);

        if ($request->hasFile('bukti_setor')) {
            $data['bukti_setor'] = $request->file('bukti_setor')->store('laporan-mingguan-bukti-setor', 'public');
        }

        LaporanMingguan::create($data);

        return redirect()
            ->route('laporan-mingguan.index', ['tahun' => $data['tahun']])
            ->with('success', 'Laporan mingguan berhasil disimpan.');
    }

    public function show(LaporanMingguan $laporan_mingguan): View
    {
        $this->ensureLaporanMingguanAccessibleByCurrentUser($laporan_mingguan);

        return view('admin.Retribusi.laporan_mingguan.show', ['laporan' => $laporan_mingguan]);
    }

    public function print(Request $request)
    {
        $tahun = $this->resolveTahun($request);
        $bulan = $this->resolveBulan($request);

        $list = LaporanMingguan::query()
            ->where('tahun', $tahun)
            ->when($this->shouldRestrictPetugasAccess(), fn ($q) => $q->whereIn('nama_petugas', $this->currentPetugasNames()))
            ->when($bulan > 0, fn ($q) => $q->whereMonth('tanggal', $bulan))
            ->orderBy('minggu_ke')
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $groupedByMinggu = $list->groupBy('minggu_ke');
        $totalJumlahKarcis = (int) $list->sum('jumlah_karcis');
        $totalLembarTerjual = (int) $list->sum('lembar_terjual');
        $totalPenjualan = (float) $list->sum('total_penjualan');
        $totalSetorKada = (float) $list->sum('setor_kada');

        $bulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $namaBulan = $bulan > 0 ? ($bulanMap[$bulan] ?? '-') : 'SEMUA BULAN';

        $pdf = Pdf::loadView('admin.Retribusi.laporan_mingguan.print', [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'namaBulan' => $namaBulan,
            'groupedByMinggu' => $groupedByMinggu,
            'totalJumlahKarcis' => $totalJumlahKarcis,
            'totalLembarTerjual' => $totalLembarTerjual,
            'totalPenjualan' => $totalPenjualan,
            'totalSetorKada' => $totalSetorKada,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('laporan-mingguan-'.$tahun.($bulan > 0 ? '-'.$bulan : '').'.pdf');
    }

    public function edit(Request $request, LaporanMingguan $laporan_mingguan): View
    {
        $this->ensureLaporanMingguanAccessibleByCurrentUser($laporan_mingguan);

        $tahun = (int) ($laporan_mingguan->tahun ?: $this->resolveTahun($request));
        [$petugasOptions, $sourceItems] = $this->buildSourceFromOperasional($tahun);

        return view('admin.Retribusi.laporan_mingguan.edit', [
            'laporan' => $laporan_mingguan,
            'petugasOptions' => $petugasOptions,
            'sourceItems' => $sourceItems,
            'tahun' => $tahun,
            'tidakAdaDataBast' => $sourceItems->isEmpty(),
        ]);
    }

    public function update(Request $request, LaporanMingguan $laporan_mingguan): RedirectResponse
    {
        $this->ensureLaporanMingguanAccessibleByCurrentUser($laporan_mingguan);

        $data = $request->validate($this->rules());
        $this->ensureLaporanPetugasAllowed((string) $data['nama_petugas']);

        if ($request->hasFile('bukti_setor')) {
            if ($laporan_mingguan->bukti_setor) {
                Storage::disk('public')->delete($laporan_mingguan->bukti_setor);
            }
            $data['bukti_setor'] = $request->file('bukti_setor')->store('laporan-mingguan-bukti-setor', 'public');
        }

        $laporan_mingguan->update($data);

        return redirect()
            ->route('laporan-mingguan.index', ['tahun' => $data['tahun']])
            ->with('success', 'Laporan mingguan berhasil diperbarui.');
    }

    public function destroy(LaporanMingguan $laporan_mingguan): RedirectResponse
    {
        $this->ensureLaporanMingguanAccessibleByCurrentUser($laporan_mingguan);

        if ($laporan_mingguan->bukti_setor) {
            Storage::disk('public')->delete($laporan_mingguan->bukti_setor);
        }

        $laporan_mingguan->delete();

        return redirect()
            ->route('laporan-mingguan.index', ['tahun' => $laporan_mingguan->tahun])
            ->with('success', 'Laporan mingguan berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'nama_petugas' => ['required', 'string', 'max:150'],
            'tempat_tugas' => ['nullable', 'string', 'max:150'],
            'nama_karcis' => ['required', 'string', 'max:255'],
            'harga_satuan' => ['required', 'numeric', 'min:0'],
            'jumlah_karcis' => ['required', 'integer', 'min:0'],
            'lembar_terjual' => ['required', 'integer', 'min:0'],
            'total_penjualan' => ['required', 'numeric', 'min:0'],
            'setor_kada' => ['required', 'numeric', 'min:0'],
            'tanggal_setor' => ['nullable', 'date'],
            'ket' => ['nullable', 'string', 'max:200'],
            'minggu_ke' => ['nullable', 'integer', 'min:1', 'max:4'],
            'bukti_setor' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    private function buildSourceFromOperasional(int $tahun): array
    {
        $operasionalRows = Operasional::query()
            ->where('tahun', $tahun)
            ->when($this->shouldRestrictPetugasAccess(), fn ($q) => $q->whereHas('penyerahan', fn ($sub) => $sub->where('pihak_kedua_id_pegawai', $this->currentPegawaiId())))
            ->with(['items' => fn ($q) => $q->latest('created_at')])
            ->get(['id', 'tahun', 'tanggal', 'nama_penanggungjawab', 'tempat_tugas']);

        $petugasOptions = $operasionalRows
            ->map(fn ($row) => [
                'nama_petugas' => $row->nama_penanggungjawab,
                'tempat_tugas' => $row->tempat_tugas,
            ])
            ->unique(fn ($row) => mb_strtolower($row['nama_petugas'].'|'.$row['tempat_tugas']))
            ->sortBy('nama_petugas')
            ->values();

        $sourceItems = $operasionalRows
            ->flatMap(function ($row): Collection {
                return $row->items->map(function ($item) use ($row): array {
                    return [
                        'nama_petugas' => $item->nama_petugas ?: $row->nama_penanggungjawab,
                        'tempat_tugas' => $row->tempat_tugas,
                        'tahun' => (int) $row->tahun,
                        'tanggal' => optional($item->tanggal_laporan ?: $item->created_at)->format('Y-m-d'),
                        'nama_karcis' => $item->nama_karcis,
                        'harga_satuan' => (float) $item->harga_satuan,
                        'jumlah_karcis' => (int) $item->lembar,
                        'lembar_terjual' => (int) $item->lembar_terjual,
                        'total_penjualan' => (float) ($item->total_penjualan ?? 0),
                    ];
                });
            })
            ->filter(fn ($row) => ! empty($row['tanggal']))
            ->values();

        return [$petugasOptions, $sourceItems];
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

    private function tahunOptions(): array
    {
        $tahunBerjalan = (int) date('Y');
        $tahunAwal = 2026;
        $tahunAkhir = max(2028, $tahunBerjalan + 1);

        return range($tahunAkhir, $tahunAwal);
    }

    private function resolveBulan(Request $request): int
    {
        $bulan = (int) $request->query('bulan', 0);

        if ($bulan < 0 || $bulan > 12) {
            return 0;
        }

        return $bulan;
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

    private function currentPetugasNames(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $names = [
            trim((string) $user->name),
            trim((string) optional($user->pegawai)->nama_lengkap),
            trim(implode(' ', array_filter([
                optional($user->pegawai)->gelar_depan,
                optional($user->pegawai)->nama_lengkap,
                optional($user->pegawai)->gelar_belakang,
            ]))),
        ];

        return collect($names)
            ->filter()
            ->unique(fn ($v) => mb_strtolower((string) $v))
            ->values()
            ->all();
    }

    private function ensureLaporanMingguanAccessibleByCurrentUser(LaporanMingguan $laporanMingguan): void
    {
        if (! $this->shouldRestrictPetugasAccess()) {
            return;
        }

        abort_unless(in_array((string) $laporanMingguan->nama_petugas, $this->currentPetugasNames(), true), 403);
    }

    private function ensureLaporanPetugasAllowed(string $namaPetugas): void
    {
        if (! $this->shouldRestrictPetugasAccess()) {
            return;
        }

        abort_unless(in_array($namaPetugas, $this->currentPetugasNames(), true), 403);
    }
}
