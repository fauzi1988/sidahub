@extends('layouts.main')
@section('container')
@php use App\Models\SuratKeluar; @endphp
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Persuratan - Manajemen TTD</h2>
         <div class="btn-actions">
            <a href="{{ route('manajemen-ttd.create') }}" class="btn btn-primary">Tambah Master TTD</a>
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
      <form method="GET" class="form-row mb-3">
         <div class="col-md-4 mb-2">
            <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari nama/pemilik/jabatan...">
         </div>
         <div class="col-md-3 mb-2">
            <select name="jenis_ttd" class="form-control">
               <option value="">-- Semua Jenis --</option>
               @foreach($jenisOptions as $key => $label)
                  <option value="{{ $key }}" @selected(request('jenis_ttd') === $key)>{{ $label }}</option>
               @endforeach
            </select>
         </div>
         <div class="col-md-2 mb-2">
            <select name="status" class="form-control">
               <option value="">-- Semua Status --</option>
               <option value="active" @selected(request('status') === 'active')>Aktif</option>
               <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
            </select>
         </div>
         <div class="col-md-3 mb-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('manajemen-ttd.index') }}" class="btn btn-primary">Reset</a>
         </div>
      </form>

      @if($list->isEmpty())
         <p class="text-muted mb-0">Belum ada master TTD.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>Nama TTD</th>
                     <th>Jenis</th>
                     <th>Pemilik</th>
                     <th>Status</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $item)
                  <tr>
                     <td>{{ $item->nama_ttd }}</td>
                     <td>{{ SuratKeluar::jenisTtdOptions()[$item->jenis_ttd] ?? $item->jenis_ttd }}</td>
                     <td>{{ $item->pemilik_ttd ?: '-' }}</td>
                     <td>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                     <td>
                        <div class="btn-actions btn-actions--compact">
                           <a href="{{ route('manajemen-ttd.show', $item) }}" class="btn btn-sm btn-primary">Detail</a>
                           <a href="{{ route('manajemen-ttd.edit', $item) }}" class="btn btn-sm btn-primary">Edit</a>
                           <form action="{{ route('manajemen-ttd.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus master TTD ini?');">
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
