<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Harian {{ $tahun }}</title>
    <style>
        @page { margin: 12mm 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; }
        .center { text-align: center; }
        .header-wrap { width: 100%; margin-bottom: 6px; border-collapse: collapse; }
        .header-wrap td { vertical-align: middle; }
        .logo-cell { width: 64px; }
        .logo { width: 52px; height: 52px; object-fit: contain; }
        .title-main { font-size: 16px; font-weight: 700; letter-spacing: .2px; }
        .title-sub { font-size: 12px; font-weight: 700; }
        .line-strong { border-top: 2px solid #000; margin: 6px 0 1px; }
        .line-thin { border-top: 1px solid #000; margin: 0 0 8px; }
        .report-title { font-size: 12px; font-weight: 700; margin-bottom: 4px; }
        .report-sub { font-size: 10px; margin-bottom: 8px; }
        .tbl { width: 100%; border-collapse: collapse; }
        .tbl th, .tbl td { border: 1px solid #333; padding: 4px 3px; }
        .tbl th { background: #e9f6f9; text-align: center; }
        .right { text-align: right; }
        .total-row td { background: #eef7ff; font-weight: 700; }
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

    <div class="center report-title">LAPORAN HARIAN OPERASIONAL KARCIS TAHUN {{ $tahun }}</div>
    <div class="center report-sub">Bulan: {{ $labelBulan }}</div>
    <div class="center report-sub">Tempat Tugas: {{ $labelTempatTugas }}</div>

    <table class="tbl">
        <thead>
            <tr>
                <th style="width:28px;">No</th>
                <th style="width:76px;">Tgl Input</th>
                <th>Petugas</th>
                <th>Tempat Tugas</th>
                <th>Nama Karcis</th>
                <th style="width:80px;">Harga Satuan</th>
                <th style="width:56px;">Lembar</th>
                <th style="width:78px;">Lembar Terpakai</th>
                <th style="width:90px;">Jumlah Hasil</th>
                <th style="width:56px;">Sisa</th>
            </tr>
        </thead>
        <tbody>
            @forelse($list as $row)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center">{{ $row->tanggal_laporan ? $row->tanggal_laporan->format('d/m/Y') : ($row->created_at ? $row->created_at->format('d/m/Y') : '-') }}</td>
                    <td>{{ $row->nama_petugas ?: '-' }}</td>
                    <td>{{ optional($row->operasional)->tempat_tugas ?: '-' }}</td>
                    <td>{{ $row->nama_karcis }}</td>
                    <td class="right">Rp {{ number_format((float) $row->harga_satuan, 0, ',', '.') }}</td>
                    <td class="right">{{ (int) $row->lembar }}</td>
                    <td class="right">{{ (int) $row->lembar_terjual }}</td>
                    <td class="right">Rp {{ number_format((float) $row->total_terjual, 0, ',', '.') }}</td>
                    <td class="right">{{ (int) $row->sisa_lembar }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="center">Tidak ada data laporan harian.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="6" class="right">TOTAL</td>
                <td class="right">{{ number_format((int) $totalLembar, 0, ',', '.') }}</td>
                <td class="right">{{ number_format((int) $totalLembarTerjual, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format((float) $totalPenjualan, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
