@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4">
         <h2 class="mb-0">Tambah Data Pendidikan</h2>
      </div>
   </div>
</div>

@if($errors->any())
   <div class="alert alert-danger">
      <ul class="mb-0 pl-3">
         @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
         @endforeach
      </ul>
   </div>
@endif

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
   </div>
@endif

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <form action="{{ route('pendidikan.store') }}" method="POST">
         @csrf
         @include('admin.Kepegawaian.pendidikan._form', ['pendidikan' => null])
      </form>
   </div>
</div>

@if(!empty($preselectPegawai))
<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <h5 class="mb-3">Data Pendidikan Tersimpan</h5>
      @if($riwayatPendidikan->isEmpty())
         <p class="text-muted mb-0">Belum ada data pendidikan untuk pegawai ini.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>Tingkat</th>
                     <th>Jurusan</th>
                     <th>Institusi</th>
                     <th>Tahun Lulus</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($riwayatPendidikan as $row)
                  <tr>
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
      @endif
   </div>
</div>
@endif
@endsection
