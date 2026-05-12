@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Tambah akun</h2>
         <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Kembali</a>
      </div>
   </div>
</div>

@if($pegawaiSiap->isEmpty())
   <div class="white_shd full margin_bottom_30">
      <div class="full graph_revenue p-4">
         <div class="alert alert-warning mb-3">
            Belum ada pegawai yang siap diberi akun. Semua pegawai di sistem mungkin sudah terhubung ke akun, atau data pegawai belum diinput.
         </div>
         <p class="mb-3">Tambah atau lengkapi data pegawai di modul <strong>Kepegawaian → Data Pegawai</strong> terlebih dahulu.</p>
         <a href="{{ route('pegawai.index') }}" class="btn btn-secondary mr-2">Daftar pegawai</a>
         <a href="{{ route('pegawai.create') }}" class="btn btn-primary">Tambah data pegawai</a>
      </div>
   </div>
@else
   <div class="white_shd full margin_bottom_30">
      <div class="full graph_revenue p-4">
         <form method="post" action="{{ route('pengguna.store') }}">
            @include('admin.pengguna._form')
            <button type="submit" class="btn btn-primary">Simpan</button>
         </form>
      </div>
   </div>
@endif
@endsection
