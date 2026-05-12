@extends('layouts.main')
@section('container')
@php $p = $pendidikan; $pg = $p->pegawai; @endphp
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Detail Pendidikan</h2>
         <div class="btn-actions">
            <a href="{{ route('pendidikan.index') }}" class="btn btn-secondary">Kembali</a>
            <a href="{{ route('pendidikan.edit', $p) }}" class="btn btn-warning">Edit</a>
         </div>
      </div>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <div class="table-responsive">
         <table class="table table-bordered">
            <tr><th width="30%">ID Pendidikan</th><td>{{ $p->id_pendidikan }}</td></tr>
            <tr><th>Pegawai</th><td>{{ $pg->nama_lengkap }}{{ $pg->nip ? ' (NIP: '.$pg->nip.')' : '' }}</td></tr>
            <tr><th>Tingkat</th><td>{{ $p->tingkat }}</td></tr>
            <tr><th>Jurusan</th><td>{{ $p->jurusan }}</td></tr>
            <tr><th>Nama Institusi</th><td>{{ $p->nama_institusi }}</td></tr>
            <tr><th>Tahun Lulus</th><td>{{ $p->tahun_lulus }}</td></tr>
         </table>
      </div>
      <div class="btn-actions mt-3">
         <a href="{{ route('pegawai.show', ['pegawai' => $pg, 'tab' => 'pendidikan']) }}" class="btn btn-info">Lihat profil pegawai</a>
      </div>
   </div>
</div>
@endsection
