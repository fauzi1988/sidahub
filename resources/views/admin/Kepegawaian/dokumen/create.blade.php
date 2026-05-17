@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4">
         <h2 class="mb-0">Upload Dokumen Pegawai</h2>
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
      <form action="{{ route('dokumen-pegawai.store') }}" method="POST" enctype="multipart/form-data">
         @csrf

         <div class="form-group">
            <label>Pegawai <span class="text-danger">*</span></label>
            @if($preselectPegawai)
               <input type="text" class="form-control" value="{{ $preselectPegawai->nama_lengkap }}{{ $preselectPegawai->nip ? ' (NIP: '.$preselectPegawai->nip.')' : '' }}" readonly disabled>
               <input type="hidden" name="id_pegawai" value="{{ old('id_pegawai', $preselectPegawai->id_pegawai) }}">
            @else
               <select name="id_pegawai" class="form-control" required>
                  <option value="">-- Pilih Pegawai --</option>
                  @foreach($pegawaiOptions as $pegawai)
                     <option value="{{ $pegawai->id_pegawai }}" @selected((string) old('id_pegawai', $preselectId) === (string) $pegawai->id_pegawai)>
                        {{ $pegawai->nama_lengkap }}{{ $pegawai->nip ? ' (NIP: '.$pegawai->nip.')' : '' }}
                     </option>
                  @endforeach
               </select>
            @endif
         </div>

         <div class="form-group">
            <label>Nama Dokumen <span class="text-danger">*</span></label>
            <input type="text" name="nama_dokumen" class="form-control" maxlength="150" value="{{ old('nama_dokumen') }}" required>
         </div>

         <div class="form-group">
            <label>Upload Dokumen <span class="text-danger">*</span></label>
            <input type="file" name="file_dokumen" class="form-control" required>
            <small class="text-muted">Maksimal 5 MB.</small>
         </div>

         <div class="mt-4 btn-actions">
            <button type="submit" class="btn btn-info">Tambah Dokumen</button>
            @if(!empty($preselectPegawai))
               <a href="{{ route('pegawai.index') }}" class="btn btn-primary">Simpan</a>
            @endif
            <a href="{{ route('pegawai.index') }}" class="btn btn-secondary">Kembali</a>
         </div>
      </form>
   </div>
</div>

@if(!empty($preselectPegawai))
<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <h5 class="mb-3">Dokumen Tersimpan</h5>
      @if($riwayatDokumen->isEmpty())
         <p class="text-muted mb-0">Belum ada dokumen untuk pegawai ini.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>Nama Dokumen</th>
                     <th>File</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($riwayatDokumen as $row)
                  <tr>
                     <td>{{ $row->nama_dokumen }}</td>
                     <td>
                        <a href="{{ asset('storage/'.$row->file_dokumen) }}" target="_blank" rel="noopener">Lihat Dokumen</a>
                     </td>
                     <td>
                        <div class="btn-actions btn-actions--compact">
                           <a href="{{ route('dokumen-pegawai.edit', $row) }}" class="btn btn-sm btn-warning">Edit</a>
                           <form action="{{ route('dokumen-pegawai.destroy', $row) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus dokumen ini?');">
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
