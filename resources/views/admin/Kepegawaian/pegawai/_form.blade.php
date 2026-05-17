@php
   $isEdit = isset($pegawai);
@endphp

<div class="form-row">
   <div class="form-group col-md-6">
      <label>NIP</label>
      <input type="text" name="nip" class="form-control" maxlength="20" value="{{ old('nip', $pegawai->nip ?? '') }}">
   </div>
   <div class="form-group col-md-6">
      <label>NIK <span class="text-danger">*</span></label>
      <input type="text" name="nik" class="form-control" maxlength="16" value="{{ old('nik', $pegawai->nik ?? '') }}" required>
   </div>
</div>

<div class="form-row">
   <div class="form-group col-md-8">
      <label>Nama Lengkap <span class="text-danger">*</span></label>
      <input type="text" name="nama_lengkap" class="form-control" maxlength="150" value="{{ old('nama_lengkap', $pegawai->nama_lengkap ?? '') }}" required>
   </div>
   <div class="form-group col-md-2">
      <label>Gelar Depan</label>
      <input type="text" name="gelar_depan" class="form-control" maxlength="20" value="{{ old('gelar_depan', $pegawai->gelar_depan ?? '') }}">
   </div>
   <div class="form-group col-md-2">
      <label>Gelar Belakang</label>
      <input type="text" name="gelar_belakang" class="form-control" maxlength="20" value="{{ old('gelar_belakang', $pegawai->gelar_belakang ?? '') }}">
   </div>
</div>

<div class="form-row">
   <div class="form-group col-md-4">
      <label>Tempat Lahir <span class="text-danger">*</span></label>
      <input type="text" name="tempat_lahir" class="form-control" maxlength="50" value="{{ old('tempat_lahir', $pegawai->tempat_lahir ?? '') }}" required>
   </div>
   <div class="form-group col-md-4">
      <label>Tanggal Lahir <span class="text-danger">*</span></label>
      <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', isset($pegawai) && $pegawai->tanggal_lahir ? $pegawai->tanggal_lahir->format('Y-m-d') : '') }}" required>
   </div>
   <div class="form-group col-md-4">
      <label>Jenis Kelamin <span class="text-danger">*</span></label>
      <select name="jenis_kelamin" class="form-control" required>
         <option value="">-- Pilih --</option>
         <option value="L" @selected(old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') === 'L')>L</option>
         <option value="P" @selected(old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') === 'P')>P</option>
      </select>
   </div>
</div>

<div class="form-row">
   <div class="form-group col-md-4">
      <label>Agama <span class="text-danger">*</span></label>
      <input type="text" name="agama" class="form-control" maxlength="20" value="{{ old('agama', $pegawai->agama ?? '') }}" required>
   </div>
   <div class="form-group col-md-4">
      <label>Status Kepegawaian <span class="text-danger">*</span></label>
      <select name="status_kepegawaian" class="form-control" required>
         <option value="">-- Pilih --</option>
         <option value="PNS" @selected(old('status_kepegawaian', $pegawai->status_kepegawaian ?? '') === 'PNS')>PNS</option>
         <option value="PPPK" @selected(old('status_kepegawaian', $pegawai->status_kepegawaian ?? '') === 'PPPK')>PPPK</option>
         <option value="Honorer/Kontrak" @selected(old('status_kepegawaian', $pegawai->status_kepegawaian ?? '') === 'Honorer/Kontrak')>Honorer/Kontrak</option>
      </select>
   </div>
   <div class="form-group col-md-4">
      <label>No HP <span class="text-danger">*</span></label>
      <input type="text" name="no_hp" class="form-control" maxlength="15" value="{{ old('no_hp', $pegawai->no_hp ?? '') }}" required>
   </div>
</div>

<div class="form-group">
   <label>Email Dinas</label>
   <input type="email" name="email_dinas" class="form-control" maxlength="100" value="{{ old('email_dinas', $pegawai->email_dinas ?? '') }}">
</div>

<div class="form-group">
   <label>Alamat KTP <span class="text-danger">*</span></label>
   <textarea name="alamat_ktp" rows="3" class="form-control" required>{{ old('alamat_ktp', $pegawai->alamat_ktp ?? '') }}</textarea>
</div>

<div class="form-group">
   <label>Foto Pegawai</label>
   <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png,.webp">
   <small class="text-muted">Format: JPG/JPEG/PNG/WEBP, maksimal 2MB.</small>
   @if(!empty($pegawai?->foto))
      <div class="mt-2">
         <img src="{{ asset('storage/'.$pegawai->foto) }}" alt="Foto Pegawai" style="max-height:120px; border-radius:8px;">
      </div>
   @endif
</div>

@if($showActions ?? true)
<div class="mt-4 btn-actions">
   <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Simpan' }}</button>
   <a href="{{ route('pegawai.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endif
