@php
   $isEdit = isset($manajemen_ttd) && $manajemen_ttd !== null;
@endphp

<div class="form-group">
   <label>Nama Master TTD <span class="text-danger">*</span></label>
   <input type="text" name="nama_ttd" class="form-control" maxlength="150" value="{{ old('nama_ttd', $isEdit ? $manajemen_ttd->nama_ttd : '') }}" required>
</div>

<div class="form-row">
   <div class="form-group col-md-4">
      <label>Jenis TTD <span class="text-danger">*</span></label>
      <select name="jenis_ttd" class="form-control" required>
         <option value="">-- Pilih Jenis --</option>
         @foreach($jenisOptions as $key => $label)
            <option value="{{ $key }}" @selected(old('jenis_ttd', $isEdit ? $manajemen_ttd->jenis_ttd : '') === $key)>{{ $label }}</option>
         @endforeach
      </select>
   </div>
   <div class="form-group col-md-4">
      <label>Pemilik TTD</label>
      <input type="text" name="pemilik_ttd" class="form-control" maxlength="150" value="{{ old('pemilik_ttd', $isEdit ? $manajemen_ttd->pemilik_ttd : '') }}">
   </div>
   <div class="form-group col-md-4">
      <label>Jabatan Pemilik</label>
      <input type="text" name="jabatan_pemilik" class="form-control" maxlength="150" value="{{ old('jabatan_pemilik', $isEdit ? $manajemen_ttd->jabatan_pemilik : '') }}">
   </div>
</div>

<div class="form-group">
   <label>File TTD (png/jpg/pdf)</label>
   <input type="file" name="file_ttd" class="form-control-file" accept=".png,.jpg,.jpeg,.pdf">
   @if($isEdit && $manajemen_ttd->file_ttd)
      <small class="text-muted d-block mt-1">File saat ini: {{ $manajemen_ttd->file_ttd }}</small>
   @endif
</div>

<div class="form-group">
   <label>Keterangan</label>
   <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $isEdit ? $manajemen_ttd->keterangan : '') }}</textarea>
</div>

<div class="form-group">
   <div class="custom-control custom-switch">
      <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $isEdit ? $manajemen_ttd->is_active : true))>
      <label class="custom-control-label" for="is_active">Aktif</label>
   </div>
</div>

<div class="mt-4 btn-actions">
   <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Simpan' }}</button>
   <a href="{{ route('manajemen-ttd.index') }}" class="btn btn-primary">Kembali</a>
</div>
