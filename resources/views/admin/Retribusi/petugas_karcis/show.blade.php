@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Detail Petugas Karcis</h2>
         <div class="btn-actions">
            <a href="{{ route('petugas-karcis.index') }}" class="btn btn-secondary">Kembali</a>
            <a href="{{ route('petugas-karcis.edit', $petugas) }}" class="btn btn-warning">Edit</a>
         </div>
      </div>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <div class="table-responsive">
         <table class="table table-bordered">
            <tr><th width="30%">Nomor Induk Pegawai</th><td>{{ $petugas->nomor_induk_pegawai }}</td></tr>
            <tr><th>Nama Pegawai</th><td>{{ $petugas->nama_pegawai }}</td></tr>
            <tr><th>Alamat</th><td>{{ $petugas->alamat }}</td></tr>
            <tr><th>Nomor Telepon</th><td>{{ $petugas->nomor_telepon }}</td></tr>
            <tr><th>Status</th><td>{{ $petugas->status }}</td></tr>
            <tr><th>Instansi</th><td>{{ $petugas->instansi ?: '-' }}</td></tr>
            <tr><th>Tempat Tugas</th><td>{{ $petugas->tempat_tugas }}</td></tr>
            <tr>
               <th>Foto</th>
               <td>
                  @if($petugas->foto)
                     <img src="{{ asset('storage/'.$petugas->foto) }}" alt="Foto {{ $petugas->nama_pegawai }}" style="max-width:200px; height:auto;" class="mb-2">
                     <br>
                     <a href="{{ asset('storage/'.$petugas->foto) }}" target="_blank" rel="noopener">Lihat ukuran penuh</a>
                  @else
                     <span class="text-muted">Belum ada foto.</span>
                  @endif
               </td>
            </tr>
         </table>
      </div>
   </div>
</div>
@endsection
