<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keluar {{ $persuratan->nomor_surat ?: $persuratan->id_surat_keluar }}</title>
    <style>
        @page { margin: 20mm 20mm 18mm 20mm; }
        body { font-family: "Times New Roman", serif; font-size: 12pt; line-height: 1.45; color: #000; }
        .center { text-align: center; }
        .right { text-align: right; }
        .header-wrap { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-wrap td { vertical-align: top; }
        .logo-cell { width: 80px; }
        .logo { width: 72px; height: 72px; object-fit: contain; }
        .title-main { font-size: 14pt; font-weight: 700; letter-spacing: .2px; }
        .title-sub { font-size: 15pt; font-weight: 700; }
        .title-address { font-size: 10.5pt; }
        .line-strong { border-top: 2px solid #000; margin: 8px 0 1px; }
        .line-thin { border-top: 1px solid #000; margin: 0 0 18px; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .meta-table td { vertical-align: top; padding: 1px 0; }
        .meta-label { width: 100px; }
        .meta-sep { width: 12px; text-align: center; }
        .title-letter { text-align: center; margin-bottom: 14px; }
        .title-letter .name { font-size: 14pt; font-weight: 700; text-decoration: underline; }
        .title-letter .number { margin-top: 2px; }
        .paragraph { text-align: justify; margin: 0 0 10px; }
        .signature-wrap { width: 100%; margin-top: 26px; border-collapse: collapse; }
        .signature-wrap td { vertical-align: top; }
        .signature-box { width: 48%; }
        .signature-space { height: 78px; }
        .bold { font-weight: 700; }
    </style>
</head>
<body>
    <table class="header-wrap">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('back/pluto/images/logo/logo_dishub.png') }}" class="logo" alt="Logo Dishub">
            </td>
            <td class="center">
                <div class="title-main">PEMERINTAH KABUPATEN HALMAHERA TIMUR</div>
                <div class="title-sub">DINAS PERHUBUNGAN</div>
                <div class="title-address">Jl. Lintas Maba Buli, Kabupaten Halmahera Timur</div>
            </td>
            <td class="logo-cell"></td>
        </tr>
    </table>
    <div class="line-strong"></div>
    <div class="line-thin"></div>

    <div class="title-letter">
        <div class="name">SURAT DINAS</div>
        <div class="number">Nomor: {{ $persuratan->nomor_surat ?: '........................................' }}</div>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Lampiran</td>
            <td class="meta-sep">:</td>
            <td>-</td>
        </tr>
        <tr>
            <td class="meta-label">Perihal</td>
            <td class="meta-sep">:</td>
            <td class="bold">{{ $persuratan->perihal }}</td>
        </tr>
    </table>

    <div class="paragraph">Kepada Yth.</div>
    <div class="paragraph" style="margin-top:-6px;">
        {{ $persuratan->tujuan_surat }}<br>
        {!! nl2br(e($persuratan->alamat_tujuan ?: 'di Tempat')) !!}
    </div>

    <div class="paragraph">Dengan hormat,</div>

    @if($persuratan->isi_surat)
        @foreach(preg_split("/(\r\n|\n|\r){2,}/", trim($persuratan->isi_surat)) as $paragraf)
            @if(trim($paragraf) !== '')
                <div class="paragraph">{!! nl2br(e(trim($paragraf))) !!}</div>
            @endif
        @endforeach
    @else
        <div class="paragraph">
            Sehubungan dengan <span class="bold">{{ strtolower($persuratan->perihal) }}</span>, bersama ini kami sampaikan surat
            dinas untuk menjadi perhatian dan tindak lanjut sebagaimana mestinya.
        </div>
    @endif

    <div class="paragraph">Demikian surat ini disampaikan, atas perhatian dan kerja samanya diucapkan terima kasih.</div>

    <table class="signature-wrap">
        <tr>
            <td></td>
            <td class="signature-box">
                <div class="right">Maba, {{ optional($persuratan->tanggal_surat)->translatedFormat('d F Y') }}</div>
                <div class="right">Kepala Dinas Perhubungan</div>
                <div class="signature-space"></div>
                <div class="right bold"><u>{{ $persuratan->penandatangan?->nama_lengkap ?: '........................................' }}</u></div>
                <div class="right">NIP. {{ $persuratan->penandatangan?->nip ?: '........................................' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
