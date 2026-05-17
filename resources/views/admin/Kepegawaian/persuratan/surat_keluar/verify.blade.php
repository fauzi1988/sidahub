<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Surat Keluar</title>
    <link rel="stylesheet" href="{{ asset('back/pluto/css/bootstrap.min.css') }}">
</head>
<body class="bg-light py-5">
   <div class="container" style="max-width:640px;">
      <div class="card shadow-sm">
         <div class="card-body">
            <h4 class="text-success mb-3">Surat Terverifikasi</h4>
            <p class="text-muted mb-4">Dokumen ini terdaftar dalam sistem Persuratan Dinas Perhubungan Kab. Halmahera Timur.</p>
            <table class="table table-sm">
               <tr><th>Nomor</th><td>{{ $persuratan->nomor_surat }}</td></tr>
               <tr><th>Tanggal</th><td>{{ optional($persuratan->tanggal_surat)->format('d F Y') }}</td></tr>
               <tr><th>Perihal</th><td>{{ $persuratan->perihal }}</td></tr>
               <tr><th>Tujuan</th><td>{{ $persuratan->tujuan_surat }}</td></tr>
               <tr><th>Status</th><td>{{ \App\Models\SuratKeluar::statusOptions()[$persuratan->status] ?? $persuratan->status }}</td></tr>
               <tr><th>Kode</th><td><code>{{ $persuratan->verification_code }}</code></td></tr>
            </table>
         </div>
      </div>
   </div>
</body>
</html>
