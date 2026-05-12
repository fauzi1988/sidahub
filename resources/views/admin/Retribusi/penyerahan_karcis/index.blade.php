@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Data Penyerahan Karcis</h2>
         <a href="{{ route('penyerahan-karcis.create') }}" class="btn btn-primary">Tambah Penyerahan</a>
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
         <p class="text-muted mb-0">Belum ada data penyerahan karcis.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>No</th>
                     <th>Tanggal</th>
                     <th>Nomor BAST</th>
                     <th>Pihak Pertama</th>
                     <th>Pihak Kedua</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $row)
                  <tr>
                     <td>{{ $list->firstItem() + $loop->index }}</td>
                     <td>{{ $row->tanggal ? $row->tanggal->format('d-m-Y') : '-' }}</td>
                     <td>{{ $row->nomor_bast }}</td>
                     <td>{{ $row->pihak_pertama_nama }}</td>
                     <td>{{ $row->pihak_kedua_nama }}</td>
                     <td>
                        <div class="btn-actions btn-actions--compact">
                           <a href="{{ route('penyerahan-karcis.show', $row) }}" class="btn btn-sm btn-secondary">Detail</a>
                           <a href="{{ route('penyerahan-karcis.print', $row) }}" class="btn btn-sm btn-info" target="_blank" rel="noopener">Cetak PDF</a>
                           <a href="{{ route('penyerahan-karcis.edit', $row) }}" class="btn btn-sm btn-warning">Edit</a>
                           <form action="{{ route('penyerahan-karcis.destroy', $row) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?');">
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
