@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Data Petugas Karcis</h2>
         <div class="btn-actions">
            <a href="{{ route('petugas-karcis.create') }}" class="btn btn-primary">Tambah Petugas</a>
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
         <p class="text-muted mb-0">Belum ada data petugas karcis.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>No</th>
                     <th>Foto</th>
                     <th>Nomor Induk Pegawai</th>
                     <th>Nama Pegawai</th>
                     <th>Nomor Telepon</th>
                     <th>Status</th>
                     <th>Instansi</th>
                     <th>Tempat Tugas</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $row)
                  <tr>
                     <td>{{ $list->firstItem() + $loop->index }}</td>
                     <td>
                        @if($row->foto)
                           <img src="{{ asset('storage/'.$row->foto) }}" alt="Foto {{ $row->nama_pegawai }}" style="width:40px; height:40px; object-fit:cover; border-radius:50%;">
                        @else
                           <span class="text-muted">-</span>
                        @endif
                     </td>
                     <td>{{ $row->nomor_induk_pegawai }}</td>
                     <td>{{ $row->nama_pegawai }}</td>
                     <td>{{ $row->nomor_telepon }}</td>
                     <td>{{ $row->status }}</td>
                     <td>{{ $row->instansi ?? '-' }}</td>
                     <td>{{ $row->tempat_tugas }}</td>
                     <td>
                        <div class="btn-actions btn-actions--compact">
                           <a href="{{ route('petugas-karcis.show', $row) }}" class="btn btn-sm btn-secondary">Detail</a>
                           <a href="{{ route('petugas-karcis.edit', $row) }}" class="btn btn-sm btn-warning">Edit</a>
                           <form action="{{ route('petugas-karcis.destroy', $row) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?');">
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
