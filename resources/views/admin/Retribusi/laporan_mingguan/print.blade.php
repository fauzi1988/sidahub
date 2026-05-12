<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Mingguan {{ $namaBulan }} {{ $tahun }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; }
        .center { text-align: center; }
        .header-wrap { width: 100%; margin-bottom: 6px; border-collapse: collapse; }
        .header-wrap td { vertical-align: middle; }
        .logo-cell { width: 64px; }
        .logo { width: 52px; height: 52px; object-fit: contain; }
        .title-main { font-size: 17px; font-weight: 700; letter-spacing: .2px; }
        .title-sub { font-size: 13px; font-weight: 700; }
        .line-strong { border-top: 2px solid #000; margin: 6px 0 1px; }
        .line-thin { border-top: 1px solid #000; margin: 0 0 10px; }
        .report-title { font-size: 13px; font-weight: 700; margin-bottom: 8px; }
        .tbl { width: 100%; border-collapse: collapse; }
        .tbl th, .tbl td { border: 1px solid #333; padding: 5px 4px; font-size: 10px; }
        .tbl th { background: #e9f6f9; text-align: center; }
        .week-row td { background: #f5f5f5; font-weight: 700; text-align: center; }
        .total-row td { background: #fff7df; font-weight: 700; }
        .grand-total td { background: #eef7ff; font-weight: 700; }
        .right { text-align: right; }
        .foot { margin-top: 14px; }
    </style>
</head>
<body>
    <table class="header-wrap">
        <tr>
            <td class="logo-cell"><img src="{{ public_path('back/pluto/images/logo/logo_dishub.png') }}" class="logo" alt="Logo"></td>
            <td class="center">
                <div class="title-main">PEMERINTAH KABUPATEN HALMAHERA TIMUR</div>
                <div class="title-sub">DINAS PERHUBUNGAN MABA</div>
            </td>
            <td class="logo-cell"></td>
        </tr>
    </table>
    <div class="line-strong"></div>
    <div class="line-thin"></div>

    <div class="center report-title">
        RINCIAN LAPORAN MINGGUAN RETRIBUSI KARCIS {{ strtoupper($namaBulan) }} TAHUN {{ $tahun }}
    </div>

    <table class="tbl">
        <thead>
            <tr>
                <th style="width:28px;">No</th>
                <th>Nama Karcis</th>
                <th style="width:78px;">Hrg Satuan</th>
                <th style="width:62px;">Jmlh Karcis</th>
                <th style="width:70px;">Lembar Terpakai</th>
                <th style="width:88px;">Total Penjualan</th>
                <th style="width:80px;">Setor KADA</th>
                <th style="width:76px;">Tgl Setoran</th>
                <th style="width:72px;">Ket</th>
            </tr>
        </thead>
        <tbody>
            @php $globalNo = 1; @endphp
            @for($minggu = 1; $minggu <= 4; $minggu++)
                @php
                    $rows = $groupedByMinggu->get($minggu, collect());
                    $weekJumlahKarcis = (int) $rows->sum('jumlah_karcis');
                    $weekLembar = (int) $rows->sum('lembar_terjual');
                    $weekTotal = (float) $rows->sum('total_penjualan');
                    $weekSetor = (float) $rows->sum('setor_kada');
                @endphp
                <tr class="week-row"><td colspan="9">MINGGU {{ ['I','II','III','IV'][$minggu - 1] }}</td></tr>
                @forelse($rows as $row)
                    <tr>
                        <td class="center">{{ $globalNo++ }}</td>
                        <td>{{ $row->nama_karcis }}</td>
                        <td class="right">Rp {{ number_format((float) $row->harga_satuan, 0, ',', '.') }}</td>
                        <td class="right">{{ $row->jumlah_karcis }}</td>
                        <td class="right">{{ $row->lembar_terjual }}</td>
                        <td class="right">Rp {{ number_format((float) $row->total_penjualan, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format((float) $row->setor_kada, 0, ',', '.') }}</td>
                        <td class="center">{{ $row->tanggal_setor ? $row->tanggal_setor->format('d/m/Y') : '-' }}</td>
                        <td>{{ $row->ket ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="center">Tidak ada data minggu ini</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="3" class="right">JUMLAH MINGGU {{ ['I','II','III','IV'][$minggu - 1] }}</td>
                    <td class="right">{{ $weekJumlahKarcis }}</td>
                    <td class="right">{{ $weekLembar }}</td>
                    <td class="right">Rp {{ number_format($weekTotal, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($weekSetor, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            @endfor

            <tr class="grand-total">
                <td colspan="3" class="right">JUMLAH TOTAL</td>
                <td class="right">{{ $totalJumlahKarcis }}</td>
                <td class="right">{{ $totalLembarTerjual }}</td>
                <td class="right">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalSetorKada, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <table class="foot" style="width:100%;">
        <tr>
            <td style="width:58%;"></td>
            <td class="center">
                Maba, {{ now()->format('d-m-Y') }}<br>
                Bendahara Penerimaan
                <div style="height:64px;"></div>
                <strong><u>.....................................</u></strong><br>
                NIP. .....................................
            </td>
        </tr>
    </table>
</body>
</html>
