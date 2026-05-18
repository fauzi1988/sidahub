@php
   $surat = $surat ?? null;
   $sifatOptions = $sifatOptions ?? \App\Models\SuratMasuk::sifatSuratOptions();
@endphp
<div class="form-row">
   <div class="form-group col-md-4">
      <label>Nomor Surat Pengirim</label>
      <input type="text" name="nomor_surat_pengirim" class="form-control" value="{{ old('nomor_surat_pengirim', $surat?->nomor_surat_pengirim) }}" maxlength="120">
   </div>
   <div class="form-group col-md-4">
      <label>Tanggal Surat <span class="text-danger">*</span></label>
      <input type="date" name="tanggal_surat" class="form-control" value="{{ old('tanggal_surat', optional($surat?->tanggal_surat)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
   </div>
   <div class="form-group col-md-4">
      <label>Tanggal Terima <span class="text-danger">*</span></label>
      <input type="date" name="tanggal_terima" class="form-control" value="{{ old('tanggal_terima', optional($surat?->tanggal_terima)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
   </div>
</div>
<div class="form-group">
   <label>Perihal <span class="text-danger">*</span></label>
   <input type="text" name="perihal" class="form-control" value="{{ old('perihal', $surat?->perihal) }}" required maxlength="255">
</div>
<div class="form-row">
   <div class="form-group col-md-8">
      <label>Pengirim <span class="text-danger">*</span></label>
      <input type="text" name="pengirim" class="form-control" value="{{ old('pengirim', $surat?->pengirim) }}" required maxlength="255">
   </div>
   <div class="form-group col-md-4">
      <label>Sifat Surat <span class="text-danger">*</span></label>
      <select name="sifat_surat" class="form-control" required>
         @foreach($sifatOptions as $key => $label)
            <option value="{{ $key }}" @selected(old('sifat_surat', $surat?->sifat_surat ?? 'biasa') === $key)>{{ $label }}</option>
         @endforeach
      </select>
   </div>
</div>
<div class="form-group">
   <label>Ringkasan / Isi Singkat</label>
   <textarea name="ringkasan" class="form-control" rows="4">{{ old('ringkasan', $surat?->ringkasan) }}</textarea>
</div>
@if(!empty($surat?->lampiran))
<div class="form-group">
   <label>Lampiran Saat Ini</label>
   <ul class="mb-2">
      @foreach($surat->lampiran as $file)
      <li>
         <label class="mb-0">
            <input type="checkbox" name="hapus_lampiran[]" value="{{ $file['path'] }}"> Hapus —
            <a href="{{ asset('storage/'.$file['path']) }}" target="_blank">{{ $file['name'] ?? 'Unduh' }}</a>
         </label>
      </li>
      @endforeach
   </ul>
</div>
@endif
<div class="form-group">
   <label>Lampiran (PDF/DOC/Gambar, max 5MB per file)</label>
   <input type="file" name="lampiran[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
</div>
@if($surat && isset($suratKeluarBalasanOptions))
<div class="form-group">
   <label>Surat Keluar Balasan (opsional)</label>
   <select name="surat_keluar_balasan_id" class="form-control">
      <option value="">-- Tidak ada / belum dibuat --</option>
      @foreach($suratKeluarBalasanOptions as $sk)
         <option value="{{ $sk->id_surat_keluar }}" @selected((string) old('surat_keluar_balasan_id', $surat->surat_keluar_balasan_id) === (string) $sk->id_surat_keluar)>
            {{ $sk->nomor_surat ?: '(belum bernomor)' }} — {{ Str::limit($sk->perihal, 50) }}
         </option>
      @endforeach
   </select>
   <small class="text-muted d-block">Hubungkan dengan surat keluar yang sudah dikirim sebagai balasan.</small>
</div>
@endif
<div class="form-group mb-0">
   <button type="submit" class="btn btn-primary">Simpan</button>
   <a href="{{ $surat ? route('persuratan-masuk.show', ['surat_masuk' => $surat, 'context' => 'sekretariat']) : route('persuratan-masuk.index') }}" class="btn btn-secondary">Batal</a>
</div>
