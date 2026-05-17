@php
   $j = $jabatan;
   $isEdit = $j !== null;
@endphp

<div class="form-row">
   <div class="form-group col-md-12">
      <label>Instansi <span class="text-danger">*</span></label>
      <input type="text" name="instansi" class="form-control" maxlength="200"
             value="{{ old('instansi', $isEdit ? ($j->instansi ?? 'Dinas Perhubungan') : 'Dinas Perhubungan') }}" required
             placeholder="Dinas Perhubungan">
      <small class="text-muted">Standar: Dinas Perhubungan — dapat diubah sesuai penugasan.</small>
   </div>
</div>

<div class="form-row">
   <div class="form-group col-md-6">
      <label>Jabatan <span class="text-danger">*</span></label>
      <input type="text" name="jabatan" class="form-control" maxlength="150"
             value="{{ old('jabatan', $isEdit ? $j->jabatan : '') }}" required>
   </div>
   <div class="form-group col-md-6">
      <label>Unit Kerja <span class="text-danger">*</span></label>
      <input type="text" name="unit_kerja" class="form-control" maxlength="150"
             value="{{ old('unit_kerja', $isEdit ? $j->unit_kerja : '') }}" required>
   </div>
</div>

@if($isEdit)
<div class="form-row">
   <div class="form-group col-md-4">
      <label>Pangkat/Golongan</label>
      <input type="text" name="pangkat_golongan" class="form-control" maxlength="100"
             value="{{ old('pangkat_golongan', $j->pangkat_golongan) }}">
   </div>
   <div class="form-group col-md-4">
      <label>TMT <span class="text-danger">*</span></label>
      <input type="date" name="tmt" class="form-control" value="{{ old('tmt', $j->tmt?->format('Y-m-d')) }}" required>
   </div>
   <div class="form-group col-md-4">
      <label>Status Jabatan <span class="text-danger">*</span></label>
      <select name="status_jabatan" class="form-control" required>
         <option value="">-- Pilih --</option>
         <option value="Aktif" @selected(old('status_jabatan', $j->status_jabatan) === 'Aktif')>Aktif</option>
         <option value="Tidak Aktif" @selected(old('status_jabatan', $j->status_jabatan) === 'Tidak Aktif')>Tidak Aktif</option>
      </select>
   </div>
</div>
@else
<div class="form-row">
   <div class="form-group col-md-6">
      <label>Karpeg</label>
      <input type="text" name="karpeg" class="form-control" maxlength="100" value="{{ old('karpeg') }}">
   </div>
   <div class="form-group col-md-6">
      <label>Pangkat/Golongan</label>
      <input type="text" name="pangkat_golongan" class="form-control" maxlength="100" value="{{ old('pangkat_golongan') }}">
   </div>
</div>
@endif
