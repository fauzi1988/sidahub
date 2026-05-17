@php
   $pd = $pendidikan;
   $isEdit = $pd !== null;
@endphp

<div class="form-row">
   <div class="form-group col-md-6">
      <label>Tingkat <span class="text-danger">*</span></label>
      <select name="tingkat" class="form-control" required>
         <option value="">-- Pilih --</option>
         @foreach(['D3', 'S1', 'S2', 'S3'] as $tk)
            <option value="{{ $tk }}" @selected(old('tingkat', $isEdit ? $pd->tingkat : null) === $tk)>{{ $tk }}</option>
         @endforeach
      </select>
   </div>
   <div class="form-group col-md-6">
      <label>Tahun Lulus <span class="text-danger">*</span></label>
      <input type="number" name="tahun_lulus" class="form-control" min="1950" max="{{ (int) date('Y') + 1 }}"
             value="{{ old('tahun_lulus', $isEdit ? $pd->tahun_lulus : '') }}" required>
   </div>
</div>

<div class="form-group">
   <label>Jurusan <span class="text-danger">*</span></label>
   <input type="text" name="jurusan" class="form-control" maxlength="150"
          value="{{ old('jurusan', $isEdit ? $pd->jurusan : '') }}"
          placeholder="Contoh: Teknik Sipil, Manajemen Transportasi Darat" required>
</div>

<div class="form-group">
   <label>Nama Institusi <span class="text-danger">*</span></label>
   <input type="text" name="nama_institusi" class="form-control" maxlength="200"
          value="{{ old('nama_institusi', $isEdit ? $pd->nama_institusi : '') }}"
          placeholder="Universitas / Sekolah" required>
</div>
