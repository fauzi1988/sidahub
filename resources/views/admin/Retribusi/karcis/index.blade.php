@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Data Karcis</h2>
         <div class="btn-actions">
            <a href="{{ route('karcis.create') }}" class="btn btn-primary">Tambah Karcis</a>
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
         <p class="text-muted mb-0">Belum ada data karcis.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>No</th>
                     <th>Kode Karcis</th>
                     <th>Nama Karcis</th>
                     <th>Harga Satuan</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $row)
                  <tr>
                     <td>{{ $list->firstItem() + $loop->index }}</td>
                     <td>{{ $row->kode_karcis }}</td>
                     <td>{{ $row->nama_karcis }}</td>
                     <td>Rp {{ number_format((float) $row->harga_satuan, 0, ',', '.') }}</td>
                     <td>
                        <div class="btn-actions btn-actions--compact">
                           <a href="{{ route('karcis.show', $row) }}" class="btn btn-sm btn-secondary">Detail</a>
                           <a href="{{ route('karcis.edit', $row) }}" class="btn btn-sm btn-warning">Edit</a>
                           <form action="{{ route('karcis.destroy', $row) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?');">
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
