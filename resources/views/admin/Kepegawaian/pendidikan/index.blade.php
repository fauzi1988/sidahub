@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Data Pendidikan Pegawai</h2>
         <div class="btn-actions">
            <a href="{{ route('pendidikan.create') }}" class="btn btn-primary">Tambah Pendidikan</a>
            <a href="{{ route('pegawai.index') }}" class="btn btn-secondary">Data Pegawai</a>
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
         <p class="text-muted mb-0">Belum ada data pendidikan.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>Nama Pegawai</th>
                     <th>Tingkat</th>
                     <th>Jurusan</th>
                     <th>Institusi</th>
                     <th>Tahun Lulus</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $row)
                  <tr>
                     <td>{{ $row->pegawai->nama_lengkap }}</td>
                     <td>{{ $row->tingkat }}</td>
                     <td>{{ $row->jurusan }}</td>
                     <td>{{ $row->nama_institusi }}</td>
                     <td>{{ $row->tahun_lulus }}</td>
                     <td>
                        <div class="btn-actions btn-actions--compact">
                           <a href="{{ route('pendidikan.show', $row) }}" class="btn btn-sm btn-secondary">Detail</a>
                           <a href="{{ route('pendidikan.edit', $row) }}" class="btn btn-sm btn-warning">Edit</a>
                           <form action="{{ route('pendidikan.destroy', $row) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?');">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                           </form>
                        </div>
                     </td>
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
