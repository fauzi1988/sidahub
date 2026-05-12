@extends('layouts.main')
@section('container')
@php
   $p = $jabatanPegawai;
   $pg = $p->pegawai;
@endphp
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Edit Jabatan</h2>
         <div class="btn-actions">
            <a href="{{ route('pegawai.show', ['pegawai' => $pg, 'tab' => 'jabatan']) }}" class="btn btn-secondary">Kembali</a>
         </div>
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
      <div class="form-group">
         <label>Pegawai</label>
         <input type="text" class="form-control" value="{{ $pg->nama_lengkap }}{{ $pg->nip ? ' (NIP: '.$pg->nip.')' : '' }}" readonly disabled>
      </div>

      <form action="{{ route('jabatan-pegawai.update', $p) }}" method="POST">
         @csrf
         @method('PUT')
         <input type="hidden" name="id_pegawai" value="{{ $p->id_pegawai }}">

         <div class="form-row">
            <div class="form-group col-md-12">
               <label>Instansi <span class="text-danger">*</span></label>
               <input type="text" name="instansi" class="form-control" maxlength="200"
                      value="{{ old('instansi', $p->instansi ?? 'Dinas Perhubungan') }}" required
                      placeholder="Dinas Perhubungan">
               <small class="text-muted">Standar: Dinas Perhubungan — dapat diubah sesuai penugasan.</small>
            </div>
         </div>

         <div class="form-row">
            <div class="form-group col-md-6">
               <label>Jabatan <span class="text-danger">*</span></label>
               <input type="text" name="jabatan" class="form-control" maxlength="150" value="{{ old('jabatan', $p->jabatan) }}" required>
            </div>
            <div class="form-group col-md-6">
               <label>Unit Kerja <span class="text-danger">*</span></label>
               <input type="text" name="unit_kerja" class="form-control" maxlength="150" value="{{ old('unit_kerja', $p->unit_kerja) }}" required>
            </div>
         </div>

         <div class="form-row">
            <div class="form-group col-md-4">
               <label>Pangkat/Golongan</label>
               <input type="text" name="pangkat_golongan" class="form-control" maxlength="100" value="{{ old('pangkat_golongan', $p->pangkat_golongan) }}">
            </div>
            <div class="form-group col-md-4">
               <label>TMT <span class="text-danger">*</span></label>
               <input type="date" name="tmt" class="form-control" value="{{ old('tmt', $p->tmt?->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group col-md-4">
               <label>Status Jabatan <span class="text-danger">*</span></label>
               <select name="status_jabatan" class="form-control" required>
                  <option value="">-- Pilih --</option>
                  <option value="Aktif" @selected(old('status_jabatan', $p->status_jabatan) === 'Aktif')>Aktif</option>
                  <option value="Tidak Aktif" @selected(old('status_jabatan', $p->status_jabatan) === 'Tidak Aktif')>Tidak Aktif</option>
               </select>
            </div>
         </div>

         <div class="mt-4 btn-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('pegawai.show', ['pegawai' => $pg, 'tab' => 'jabatan']) }}" class="btn btn-secondary">Batal</a>
         </div>
      </form>
   </div>
</div>
@endsection
