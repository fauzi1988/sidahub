@php
   $isEdit = isset($petugas) && $petugas !== null;
@endphp

<div class="form-group">
   <label>Nomor Induk Pegawai <span class="text-danger">*</span></label>
   <input type="text" name="nomor_induk_pegawai" class="form-control" maxlength="50" value="{{ old('nomor_induk_pegawai', $isEdit ? $petugas->nomor_induk_pegawai : '') }}" required>
</div>

<div class="form-group">
   <label>Nama Pegawai <span class="text-danger">*</span></label>
   <input type="text" name="nama_pegawai" class="form-control" maxlength="150" value="{{ old('nama_pegawai', $isEdit ? $petugas->nama_pegawai : '') }}" required>
</div>

<div class="form-group">
   <label>Alamat <span class="text-danger">*</span></label>
   <textarea name="alamat" class="form-control" rows="3" required>{{ old('alamat', $isEdit ? $petugas->alamat : '') }}</textarea>
</div>

<div class="form-group">
   <label>Nomor Telepon <span class="text-danger">*</span></label>
   <input type="text" name="nomor_telepon" class="form-control" maxlength="20" value="{{ old('nomor_telepon', $isEdit ? $petugas->nomor_telepon : '') }}" required>
</div>

<div class="form-group">
   <label>Status <span class="text-danger">*</span></label>
   <select name="status" class="form-control" required>
      <option value="">-- Pilih Status --</option>
      @foreach(['PNS', 'PPPK', 'Tenaga Kontrak'] as $status)
         <option value="{{ $status }}" @selected(old('status', $isEdit ? $petugas->status : '') === $status)>{{ $status }}</option>
      @endforeach
   </select>
</div>

<div class="form-group">
   <label>Instansi <span class="text-danger">*</span></label>
   <input type="text" name="instansi" class="form-control" maxlength="200" value="{{ old('instansi', $isEdit ? $petugas->instansi : '') }}" required>
</div>

<div class="form-group">
   <label>Tempat Tugas <span class="text-danger">*</span></label>
   <input type="text" name="tempat_tugas" class="form-control" maxlength="150" value="{{ old('tempat_tugas', $isEdit ? $petugas->tempat_tugas : '') }}" required>
</div>

<div class="form-group">
   <label>Foto</label>
   <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png">
   @if($isEdit && $petugas->foto)
      <small class="d-block mt-1">Foto saat ini: <a href="{{ asset('storage/'.$petugas->foto) }}" target="_blank" rel="noopener">Lihat Foto</a></small>
   @else
      <small class="d-block mt-1">Format: JPG/JPEG/PNG, maksimal 5MB.</small>
   @endif
</div>

<div class="mt-4 btn-actions">
   <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Perbarui' : 'Simpan' }}</button>
   <a href="{{ route('petugas-karcis.index') }}" class="btn btn-secondary">Kembali</a>
</div>
