<?php

namespace App\Http\Controllers;

use App\Models\OperasionalItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LaporanHarianController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);
        $baseQuery = $this->buildQuery($filters['tahun'], $filters['bulan'], $filters['tempat_tugas']);

        $list = (clone $baseQuery)
            ->orderByDesc('tanggal_laporan')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $summaryQuery = (clone $baseQuery);
        $totalItem = (int) (clone $summaryQuery)->count();
        $totalLembar = (int) (clone $summaryQuery)->sum('lembar');
        $totalLembarTerjual = (int) (clone $summaryQuery)->sum('lembar_terjual');
        $totalPenjualan = (float) (clone $summaryQuery)->sum('total_terjual');

        $tempatTugasOptions = $this->tempatTugasOptions($filters['tahun'], $filters['bulan']);
        $tahunOptions = $this->tahunOptions();

        return view('admin.Retribusi.laporan_harian.index', [
            'list' => $list,
            'tahun' => $filters['tahun'],
            'bulan' => $filters['bulan'],
            'tempatTugas' => $filters['tempat_tugas'],
            'tahunOptions' => $tahunOptions,
            'tempatTugasOptions' => $tempatTugasOptions,
            'totalItem' => $totalItem,
            'totalLembar' => $totalLembar,
            'totalLembarTerjual' => $totalLembarTerjual,
            'totalPenjualan' => $totalPenjualan,
        ]);
    }

    public function print(Request $request)
    {
        $filters = $this->resolveFilters($request);
        $list = $this->buildQuery($filters['tahun'], $filters['bulan'], $filters['tempat_tugas'])
            ->orderByDesc('tanggal_laporan')
            ->orderByDesc('id')
            ->get();

        $totalLembar = (int) $list->sum('lembar');
        $totalLembarTerjual = (int) $list->sum('lembar_terjual');
        $totalPenjualan = (float) $list->sum('total_terjual');
        $labelTempatTugas = $filters['tempat_tugas'] !== '' ? $filters['tempat_tugas'] : 'SEMUA TEMPAT TUGAS';
        $labelBulan = $this->labelBulan($filters['bulan']);

        $pdf = Pdf::loadView('admin.Retribusi.laporan_harian.print', [
            'list' => $list,
            'tahun' => $filters['tahun'],
            'bulan' => $filters['bulan'],
            'labelBulan' => $labelBulan,
            'tempatTugas' => $filters['tempat_tugas'],
            'labelTempatTugas' => $labelTempatTugas,
            'totalLembar' => $totalLembar,
            'totalLembarTerjual' => $totalLembarTerjual,
            'totalPenjualan' => $totalPenjualan,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream(
            'laporan-harian-'
            .$filters['tahun']
            .($filters['bulan'] ? '-bulan-'.str_pad((string) $filters['bulan'], 2, '0', STR_PAD_LEFT) : '')
            .($filters['tempat_tugas'] !== '' ? '-'.str_replace(' ', '-', strtolower($filters['tempat_tugas'])) : '')
            .'.pdf'
        );
    }

    private function buildQuery(int $tahun, ?int $bulan, string $tempatTugas)
    {
        return OperasionalItem::query()
            ->with('operasional:id,tempat_tugas,tahun')
            ->whereHas('operasional', function ($q) use ($tahun, $tempatTugas) {
                $q->where('tahun', $tahun)
                    ->when($tempatTugas !== '', fn ($sub) => $sub->where('tempat_tugas', $tempatTugas))
                    ->when($this->shouldRestrictPetugasAccess(), fn ($sub) => $sub->whereHas('penyerahan', fn ($p) => $p->where('pihak_kedua_id_pegawai', $this->currentPegawaiId())));
            })
            ->when($bulan !== null, function ($q) use ($bulan) {
                $q->whereMonth('tanggal_laporan', $bulan);
            });
    }

    private function tempatTugasOptions(int $tahun, ?int $bulan): array
    {
        return OperasionalItem::query()
            ->whereHas('operasional', function ($q) use ($tahun) {
                $q->where('tahun', $tahun)
                    ->when($this->shouldRestrictPetugasAccess(), fn ($sub) => $sub->whereHas('penyerahan', fn ($p) => $p->where('pihak_kedua_id_pegawai', $this->currentPegawaiId())));
            })
            ->when($bulan !== null, fn ($q) => $q->whereMonth('tanggal_laporan', $bulan))
            ->join('operasional', 'operasional.id', '=', 'operasional_item.operasional_id')
            ->whereNotNull('operasional.tempat_tugas')
            ->where('operasional.tempat_tugas', '<>', '')
            ->distinct()
            ->orderBy('operasional.tempat_tugas')
            ->pluck('operasional.tempat_tugas')
            ->values()
            ->all();
    }

    private function resolveFilters(Request $request): array
    {
        $tahun = $this->resolveTahun($request);
        $bulan = $this->resolveBulan($request);
        $tempatTugas = trim((string) $request->query('tempat_tugas', ''));

        return [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'tempat_tugas' => $tempatTugas,
        ];
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

    private function resolveBulan(Request $request): ?int
    {
        $rawBulan = $request->query('bulan');

        if ($rawBulan === null || $rawBulan === '') {
            return null;
        }

        $bulan = (int) $rawBulan;

        if ($bulan < 1 || $bulan > 12) {
            return null;
        }

        return $bulan;
    }

    private function tahunOptions(): array
    {
        $tahunBerjalan = (int) date('Y');
        $tahunAwal = 2026;
        $tahunAkhir = max(2028, $tahunBerjalan + 1);

        return range($tahunAkhir, $tahunAwal);
    }

    private function labelBulan(?int $bulan): string
    {
        $labels = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $bulan !== null ? ($labels[$bulan] ?? 'Semua Bulan') : 'Semua Bulan';
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
}
