@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Data Pegawai</h2>
         <div class="btn-actions">
            <a href="{{ route('pegawai.create') }}" class="btn btn-primary">Tambah Pegawai</a>
         </div>
      </div>
   </div>
</div>

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
   </div>
@endif

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      @if($list->isEmpty())
         <p class="text-muted mb-0">Belum ada data pegawai.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>NIP</th>
                     <th>NIK</th>
                     <th>Nama</th>
                     <th>Jenis Kelamin</th>
                     <th>Status</th>
                     <th>No HP</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $pegawai)
                  <tr>
                     <td>{{ $pegawai->nip ?: '-' }}</td>
                     <td>{{ $pegawai->nik }}</td>
                     <td>{{ trim(($pegawai->gelar_depan ? $pegawai->gelar_depan.' ' : '').$pegawai->nama_lengkap.($pegawai->gelar_belakang ? ', '.$pegawai->gelar_belakang : '')) }}</td>
                     <td>{{ $pegawai->jenis_kelamin }}</td>
                     <td>{{ $pegawai->status_kepegawaian }}</td>
                     <td>{{ $pegawai->no_hp }}</td>
                     <td><div class="btn-actions btn-actions--compact">
                        <a href="{{ route('pegawai.show', $pegawai) }}" class="btn btn-sm btn-secondary">Detail</a>
                        <a href="{{ route('pegawai.edit', $pegawai) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('pegawai.destroy', $pegawai) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data pegawai ini?');">
                           @csrf
                           @method('DELETE')
                           <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                     </div></td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
         <div class="mt-3">{{ $list->links() }}</div>
      @endif
   </div>
</div>
@endsection
