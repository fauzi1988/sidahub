@php
   $isEdit = isset($karcis) && $karcis !== null;
@endphp

<div class="form-group">
   <label>Kode Karcis <span class="text-danger">*</span></label>
   <input type="text" name="kode_karcis" class="form-control" maxlength="30" value="{{ old('kode_karcis', $isEdit ? $karcis->kode_karcis : '') }}" placeholder="Contoh: KRC-0001" required>
</div>

<div class="form-group">
   <label>Nama Karcis <span class="text-danger">*</span></label>
   <input type="text" name="nama_karcis" class="form-control" maxlength="255" value="{{ old('nama_karcis', $isEdit ? $karcis->nama_karcis : '') }}" placeholder="Contoh: Karcis Parkir Motor" required>
</div>

<div class="form-group">
   <label>Harga Satuan (Rp) <span class="text-danger">*</span></label>
   <input type="number" name="harga_satuan" class="form-control" step="0.01" min="0" value="{{ old('harga_satuan', $isEdit ? $karcis->harga_satuan : '') }}" placeholder="0" required>
</div>

<div class="mt-4 btn-actions">
   <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Perbarui' : 'Simpan' }}</button>
   <a href="{{ route('karcis.index') }}" class="btn btn-secondary">Kembali</a>
</div>
