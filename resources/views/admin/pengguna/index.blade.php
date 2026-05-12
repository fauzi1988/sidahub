@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Manajemen Akun</h2>
         <a href="{{ route('pengguna.create') }}" class="btn btn-primary">Tambah akun</a>
      </div>
   </div>
</div>

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
   </div>
@endif
@if(session('error'))
   <div class="alert alert-danger alert-dismissible fade show">
      {{ session('error') }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
   </div>
@endif

<div class="white_shd full margin_bottom_30 mb-3">
   <div class="full graph_revenue p-3">
      <form method="get" action="{{ route('pengguna.index') }}" class="form-inline">
         <input type="search" name="q" value="{{ request('q') }}" class="form-control mr-2 mb-2"
                placeholder="Cari nama, email, NIP, NIK…">
         <button type="submit" class="btn btn-secondary mb-2">Cari</button>
         @if(request()->filled('q'))
            <a href="{{ route('pengguna.index') }}" class="btn btn-light mb-2 ml-sm-2">Reset</a>
         @endif
      </form>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      @if($users->isEmpty())
         <p class="text-muted mb-0">Belum ada akun pengguna.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>No</th>
                     <th>Pegawai</th>
                     <th>Nama akun</th>
                     <th>Email</th>
                     <th>Peran</th>
                     <th width="200">Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($users as $row)
                  <tr>
                     <td>{{ $users->firstItem() + $loop->index }}</td>
                     <td>
                        @if($row->pegawai)
                           <span class="d-block"><small class="text-muted">NIP</small> {{ $row->pegawai->nip ?: '—' }}</span>
                           <span>{{ trim(implode(' ', array_filter([$row->pegawai->gelar_depan, $row->pegawai->nama_lengkap, $row->pegawai->gelar_belakang]))) }}</span>
                        @else
                           <span class="text-muted">—</span>
                        @endif
                     </td>
                     <td>{{ $row->name }}</td>
                     <td>{{ $row->email }}</td>
                     <td>
                        @if($row->is_super_admin)
                           <span class="badge badge-warning text-dark">Super admin</span>
                        @else
                           <span class="badge badge-info">Terbatas</span>
                        @endif
                     </td>
                     <td>
                        <div class="btn-actions btn-actions--compact">
                           <a href="{{ route('pengguna.edit', $row) }}" class="btn btn-sm btn-warning">Edit</a>
                           <form action="{{ route('pengguna.destroy', $row) }}" method="POST" class="d-inline"
                                 onsubmit="return confirm('Yakin hapus akun ini?');">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-danger" @if(auth()->id() === $row->id) disabled title="Tidak dapat menghapus akun sendiri" @endif>Hapus</button>
                           </form>
                        </div>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
         <div class="d-flex justify-content-center">
            {{ $users->links('pagination::bootstrap-4') }}
         </div>
      @endif
   </div>
</div>
@endsection
