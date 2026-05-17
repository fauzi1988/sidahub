@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4">
         <h2 class="mb-0">Edit Dokumen Pegawai</h2>
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

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <form action="{{ route('dokumen-pegawai.update', $dokumen) }}" method="POST" enctype="multipart/form-data">
         @csrf
         @method('PUT')

         <div class="form-group">
            <label>Pegawai</label>
            <input type="text" class="form-control" value="{{ $dokumen->pegawai->nama_lengkap }}{{ $dokumen->pegawai->nip ? ' (NIP: '.$dokumen->pegawai->nip.')' : '' }}" readonly disabled>
         </div>

         <div class="form-group">
            <label>Nama Dokumen <span class="text-danger">*</span></label>
            <input type="text" name="nama_dokumen" class="form-control" maxlength="150" value="{{ old('nama_dokumen', $dokumen->nama_dokumen) }}" required>
         </div>

         <div class="form-group">
            <label>Upload Dokumen</label>
            <input type="file" name="file_dokumen" class="form-control">
            <small class="text-muted">Kosongkan jika tidak ingin mengganti file. Maksimal 5 MB.</small>
            @if($dokumen->file_dokumen)
               <div class="mt-2">
                  <a href="{{ asset('storage/'.$dokumen->file_dokumen) }}" target="_blank" rel="noopener">Lihat file saat ini</a>
               </div>
            @endif
         </div>

         <div class="mt-4 btn-actions">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('dokumen-pegawai.create', ['id_pegawai' => $dokumen->id_pegawai]) }}" class="btn btn-secondary">Batal</a>
         </div>
      </form>
   </div>
</div>
@endsection
