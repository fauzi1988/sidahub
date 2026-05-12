<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BAST Karcis {{ $penyerahan->nomor_bast }}</title>
    <style>
        @page { margin: 19mm 15mm 18mm 17mm; }
        body { font-family: DejaVu Serif, serif; font-size: 11.5px; line-height: 1.28; color: #000; }
        .sheet { margin-left: 2mm; margin-right: 1mm; }
        .center { text-align: center; }
        .justify { text-align: justify; }
        .header-wrap { width: 100%; margin-bottom: 6px; }
        .header-wrap td { vertical-align: middle; }
        .logo-cell { width: 32px; padding-right: 0; }
        .logo { width: 66px; height: 66px; object-fit: contain; margin-left: 0; }
        .title-top { font-size: 23px; font-weight: bold; margin-top: 0; letter-spacing: 0.2px; }
        .sub { font-size: 10.5px; }
        .line-strong { border-top: 2.4px solid #000; margin: 7px 0 1px; }
        .line-thin { border-top: 1px solid #000; margin: 0 0 12px; }
        .doc-title { font-size: 14px; font-weight: bold; text-decoration: underline; letter-spacing: 0.2px; }
        .doc-number { margin-top: 2px; margin-bottom: 12px; }
        .party { width: 100%; margin: 0 0 4px; }
        .party td { padding: 0; vertical-align: top; }
        .party .idx { width: 18px; }
        .party .label { width: 96px; }
        .party .colon { width: 10px; text-align: center; }
        .mb-10 { margin-bottom: 11px; }
        .mb-14 { margin-bottom: 15px; }
        .sign { width: 100%; margin-top: 16px; }
        .sign td { width: 50%; text-align: center; vertical-align: top; }
        .sign-space { height: 66px; }
        .page-break { page-break-before: always; }
        .tbl { width: 100%; border-collapse: collapse; }
        .tbl th, .tbl td { border: 1.2px solid #000; padding: 3px 4px; vertical-align: top; font-size: 10.8px; }
        .tbl th { text-align: center; font-weight: bold; padding-top: 5px; padding-bottom: 5px; }
    </style>
</head>
<body>
<div class="sheet">
    @php
        $tgl = $penyerahan->tanggal;

        $hariMap = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => "Jum'at",
            'Saturday' => 'Sabtu',
        ];

        $bulanMap = [
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

        $penyebut = function (int $nilai) use (&$penyebut): string {
            $nilai = abs($nilai);
            $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

            if ($nilai < 12) {
                return $huruf[$nilai];
            }
            if ($nilai < 20) {
                return $penyebut($nilai - 10).' Belas';
            }
            if ($nilai < 100) {
                return $penyebut(intdiv($nilai, 10)).' Puluh'.($nilai % 10 !== 0 ? ' '.$penyebut($nilai % 10) : '');
            }
            if ($nilai < 200) {
                return 'Seratus'.($nilai - 100 !== 0 ? ' '.$penyebut($nilai - 100) : '');
            }
            if ($nilai < 1000) {
                return $penyebut(intdiv($nilai, 100)).' Ratus'.($nilai % 100 !== 0 ? ' '.$penyebut($nilai % 100) : '');
            }
            if ($nilai < 2000) {
                return 'Seribu'.($nilai - 1000 !== 0 ? ' '.$penyebut($nilai - 1000) : '');
            }
            if ($nilai < 1000000) {
                return $penyebut(intdiv($nilai, 1000)).' Ribu'.($nilai % 1000 !== 0 ? ' '.$penyebut($nilai % 1000) : '');
            }

            return (string) $nilai;
        };

        $hariNama = $tgl ? ($hariMap[$tgl->format('l')] ?? $tgl->format('l')) : '-';
        $tanggalAngka = $tgl ? $tgl->format('d-m-Y') : '-';
        $tanggalTerbilang = $tgl ? $penyebut((int) $tgl->format('d')) : '-';
        $bulanNama = $tgl ? ($bulanMap[(int) $tgl->format('m')] ?? $tgl->format('F')) : '-';
        $tahunTerbilang = $tgl ? $penyebut((int) $tgl->format('Y')) : '-';
    @endphp
    <table class="header-wrap">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('back/pluto/images/logo/logo_dishub.png') }}" class="logo" alt="Logo Dishub">
            </td>
            <td class="center">
                <div style="font-size: 15px; font-weight: bold; margin-bottom: 1px;">PEMERINTAH KABUPATEN HALMAHERA TIMUR</div>
                <div class="title-top">DINAS PERHUBUNGAN</div>
                <div class="sub">Jalan Kawasan Pemerintahan Kota Maba, Halmahera Timur 97862</div>
                <div class="sub">Laman dishub.haltimkab.go.id, Pos-el dishub.haltimkab@gmail.com</div>
            </td>
        </tr>
    </table>
    <div class="line-strong"></div>
    <div class="line-thin"></div>

    <div class="center doc-title">BERITA ACARA SERAH TERIMA BARANG</div>
    <div class="center doc-number">Nomor : {{ $penyerahan->nomor_bast }}</div>

    <p class="justify mb-10" style="text-indent: 28px;">
        Pada harì ini, {{ $hariNama }} Tanggal {{ $tanggalTerbilang }} Bulan {{ $bulanNama }} Tahun {{ $tahunTerbilang }}
        ({{ $tanggalAngka }}), Kami yang bertanda tangan dibawah ini :
    </p>

    <table class="party">
        <tr>
            <td class="idx"><strong>1.</strong></td>
            <td class="label">Nama</td><td class="colon">:</td><td>{{ $penyerahan->pihak_pertama_nama }}</td>
        </tr>
        <tr><td></td><td class="label">NIP</td><td class="colon">:</td><td>{{ $penyerahan->pihak_pertama_nip ?: '-' }}</td></tr>
        <tr><td></td><td class="label">Jabatan</td><td class="colon">:</td><td>{{ $penyerahan->pihak_pertama_jabatan ?: '-' }}</td></tr>
        <tr><td></td><td class="label">Instansi</td><td class="colon">:</td><td>{{ $penyerahan->pihak_pertama_instansi ?: '-' }}</td></tr>
        <tr><td></td><td class="label">Alamat</td><td class="colon">:</td><td>{{ $penyerahan->pihak_pertama_alamat ?: '-' }}</td></tr>
    </table>
    <p class="mb-10">Selanjutnya disebut <strong>"PIHAK PERTAMA"</strong></p>

    <table class="party">
        <tr>
            <td class="idx"><strong>2.</strong></td>
            <td class="label">Nama</td><td class="colon">:</td><td>{{ $penyerahan->pihak_kedua_nama }}</td>
        </tr>
        <tr><td></td><td class="label">NIP</td><td class="colon">:</td><td>{{ $penyerahan->pihak_kedua_nip ?: '-' }}</td></tr>
        <tr><td></td><td class="label">Jabatan</td><td class="colon">:</td><td>{{ $penyerahan->pihak_kedua_jabatan ?: '-' }}</td></tr>
        <tr><td></td><td class="label">Tempat Tugas</td><td class="colon">:</td><td>{{ $penyerahan->pihak_kedua_tempat_tugas ?: '-' }}</td></tr>
        <tr><td></td><td class="label">Instansi</td><td class="colon">:</td><td>{{ $penyerahan->pihak_kedua_instansi ?: '-' }}</td></tr>
        <tr><td></td><td class="label">Alamat</td><td class="colon">:</td><td>{{ $penyerahan->pihak_kedua_alamat ?: '-' }}</td></tr>
    </table>
    <p class="mb-10">Selanjutnya disebut <strong>"PIHAK KEDUA"</strong></p>

    <p class="justify mb-14" style="text-indent: 28px;">
        Dengan ini menerangkan bahwa <strong>"PIHAK PERTAMA"</strong> telah menyerahkan karcis kepada
        <strong>"PIHAK KEDUA"</strong> dalam keadaan baik dan apabila dikemudian hari terdapat kerusakan
        menjadi tanggung jawab <strong>"PIHAK KEDUA"</strong>. Diharapkan puntung karcis dikembalikan kepada
        bendahara penerimaan sebagai laporan.
    </p>
    <p class="mb-14">Demikian berita acara ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

    <table class="sign">
        <tr>
            <td>Pihak Kedua</td>
            <td>Pihak Pertama</td>
        </tr>
        <tr><td class="sign-space"></td><td></td></tr>
        <tr>
            <td><strong><u>{{ $penyerahan->pihak_kedua_nama }}</u></strong><br>NIP. {{ $penyerahan->pihak_kedua_nip ?: '-' }}</td>
            <td><strong><u>{{ $penyerahan->pihak_pertama_nama }}</u></strong><br>NIP. {{ $penyerahan->pihak_pertama_nip ?: '-' }}</td>
        </tr>
    </table>

    @if($penyerahan->mengetahui_nama)
        <div class="center" style="margin-top: 10px;">
            Mengetahui,<br>
            Kepala Dinas Perhubungan<br>
            Kabupaten Halmahera Timur
            <div style="height: 44px;"></div>
            <strong><u>{{ $penyerahan->mengetahui_nama }}</u></strong><br>
            NIP. {{ $penyerahan->mengetahui_nip ?: '-' }}
        </div>
    @endif

    <div class="page-break"></div>

    <div style="font-size: 13px; margin-bottom: 6px;"><strong>Lampiran</strong></div>
    <table style="margin-bottom: 10px;">
        <tr>
            <td style="width:70px;">Nomor</td><td style="width:12px;">:</td><td>{{ $penyerahan->nomor_bast }}</td>
        </tr>
        <tr>
            <td>Tanggal</td><td>:</td><td>{{ optional($penyerahan->tanggal)->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <table class="tbl">
        <thead>
            <tr>
                <th style="width:24px;">No</th>
                <th>Uraian</th>
                <th style="width:72px;">Harga Satuan</th>
                <th style="width:52px;">Lembar</th>
                <th style="width:70px;">Total</th>
                <th style="width:96px;">Nomor Seri Awal</th>
                <th style="width:96px;">Nomor Seri Akhir</th>
                <th style="width:78px;">Ket</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penyerahan->items as $item)
                <tr>
                    <td style="text-align:center;">{{ $loop->iteration }}</td>
                    <td>
                        {{ $item->karcis->nama_karcis ?? $item->uraian }}
                    </td>
                    <td style="text-align:center;">
                        {{ $item->harga_satuan ? 'Rp. '.number_format((float) $item->harga_satuan, 0, ',', '.') : '-' }}
                    </td>
                    <td style="text-align:center;">{{ $item->lembar ?? '-' }}</td>
                    <td style="text-align:center;">
                        {{ $item->total ? 'Rp. '.number_format((float) $item->total, 0, ',', '.') : '-' }}
                    </td>
                    <td style="text-align:center;">{{ $item->nomor_seri_awal }}</td>
                    <td style="text-align:center;">{{ $item->nomor_seri_akhir }}</td>
                    <td style="text-align:center;">{{ $item->keterangan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="center" style="margin-top: 32px;">
        Pengurus Barang
        <div style="height: 92px;"></div>
        <strong><u>{{ $penyerahan->pihak_pertama_nama }}</u></strong><br>
        NIP. {{ $penyerahan->pihak_pertama_nip ?: '-' }}
    </div>
</div>
</body>
</html>
