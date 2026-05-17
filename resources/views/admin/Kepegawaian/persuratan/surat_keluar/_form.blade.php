@php
   use App\Models\SuratKeluar;
   $isEdit = isset($persuratan) && $persuratan !== null;
   $jenisSurat = SuratKeluar::jenisSuratOptions();
   $sifatSurat = SuratKeluar::sifatSuratOptions();
   $prioritas = SuratKeluar::prioritasOptions();
   $isiTemplates = $isiTemplates ?? SuratKeluar::isiTemplates();
@endphp

@if($isEdit)
   <div class="alert alert-info py-2">
      Status saat ini: <strong>{{ SuratKeluar::statusOptions()[$persuratan->status] ?? $persuratan->status }}</strong>
      — perubahan status hanya melalui aksi workflow di halaman detail.
   </div>
@endif

<div class="form-row">
   <div class="form-group col-md-6">
      <label>Nomor Surat</label>
      <input type="text" class="form-control" value="{{ $isEdit ? ($persuratan->nomor_surat ?: 'Belum dinomori') : 'Otomatis setelah approval Kadis' }}" readonly disabled>
      <small class="text-muted">Diisi Sekretariat setelah surat ditandatangani Kadis.</small>
   </div>
   <div class="form-group col-md-3">
      <label>Tanggal Surat <span class="text-danger">*</span></label>
      <input type="date" name="tanggal_surat" class="form-control" value="{{ old('tanggal_surat', $isEdit && $persuratan->tanggal_surat ? $persuratan->tanggal_surat->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
   </div>
   @if($isEdit && $persuratan->tanggal_kirim)
   <div class="form-group col-md-3">
      <label>Tanggal Kirim</label>
      <input type="text" class="form-control" value="{{ $persuratan->tanggal_kirim->format('d-m-Y') }}" readonly disabled>
   </div>
   @endif
</div>

<div class="form-group">
   <label>Perihal <span class="text-danger">*</span></label>
   <input type="text" name="perihal" class="form-control" maxlength="255" value="{{ old('perihal', $isEdit ? $persuratan->perihal : '') }}" required>
</div>

<div class="form-row">
   <div class="form-group col-md-8">
      <label>Tujuan Surat <span class="text-danger">*</span></label>
      <input type="text" name="tujuan_surat" class="form-control" maxlength="255" value="{{ old('tujuan_surat', $isEdit ? $persuratan->tujuan_surat : '') }}" placeholder="Nama instansi / pihak penerima" required>
   </div>
   @if($isEdit && $persuratan->unit_kerja)
   <div class="form-group col-md-4">
      <label>Unit Kerja Pengusul</label>
      <input type="text" class="form-control" value="{{ $persuratan->unit_kerja }}" readonly disabled>
   </div>
   @endif
</div>

<div class="form-group">
   <label>Alamat Tujuan</label>
   <textarea name="alamat_tujuan" class="form-control" rows="2">{{ old('alamat_tujuan', $isEdit ? $persuratan->alamat_tujuan : '') }}</textarea>
</div>

<div class="form-row">
   <div class="form-group col-md-4">
      <label>Jenis Surat <span class="text-danger">*</span></label>
      <select name="jenis_surat" id="jenis_surat" class="form-control" required>
         @foreach($jenisSurat as $key => $label)
            <option value="{{ $key }}" @selected(old('jenis_surat', $isEdit ? $persuratan->jenis_surat : 'surat_dinas') === $key)>{{ $label }}</option>
         @endforeach
      </select>
   </div>
   <div class="form-group col-md-4">
      <label>Sifat Surat <span class="text-danger">*</span></label>
      <select name="sifat_surat" class="form-control" required>
         @foreach($sifatSurat as $key => $label)
            <option value="{{ $key }}" @selected(old('sifat_surat', $isEdit ? $persuratan->sifat_surat : 'biasa') === $key)>{{ $label }}</option>
         @endforeach
      </select>
   </div>
   <div class="form-group col-md-4">
      <label>Prioritas <span class="text-danger">*</span></label>
      <select name="prioritas" class="form-control" required>
         @foreach($prioritas as $key => $label)
            <option value="{{ $key }}" @selected(old('prioritas', $isEdit ? $persuratan->prioritas : 'normal') === $key)>{{ $label }}</option>
         @endforeach
      </select>
   </div>
</div>

<div class="form-row">
   <div class="form-group col-md-6">
      <label>Pegawai Pengusul</label>
      @if(! $isEdit && !empty($userPegawai))
         <input type="text" class="form-control" value="{{ $userPegawai->nama_lengkap }}" readonly disabled>
         <input type="hidden" name="id_pegawai_pengusul" value="{{ $userPegawai->id_pegawai }}">
      @else
         <select name="id_pegawai_pengusul" class="form-control">
            <option value="">-- Pilih Pegawai --</option>
            @foreach($pegawaiOptions as $pegawai)
               <option value="{{ $pegawai->id_pegawai }}" @selected((string) old('id_pegawai_pengusul', $isEdit ? $persuratan->id_pegawai_pengusul : '') === (string) $pegawai->id_pegawai)>
                  {{ $pegawai->nama_lengkap }}{{ $pegawai->nip ? ' (NIP: '.$pegawai->nip.')' : '' }}
               </option>
            @endforeach
         </select>
      @endif
   </div>
   <div class="form-group col-md-6">
      <label>Penandatangan (Kepala Dinas)</label>
      @if(! $isEdit)
         @php
            $selectedKadisId = old('id_pegawai_penandatangan', isset($kadisPegawai) && $kadisPegawai ? $kadisPegawai->id_pegawai : '');
            $selectedKadisLabel = isset($kadisPegawai) && $kadisPegawai
               ? $kadisPegawai->nama_lengkap.($kadisPegawai->nip ? ' (NIP: '.$kadisPegawai->nip.')' : '')
               : 'Kepala Dinas belum tersedia';
         @endphp
         <input type="text" class="form-control" value="{{ $selectedKadisLabel }}" readonly disabled>
         <input type="hidden" name="id_pegawai_penandatangan" value="{{ $selectedKadisId }}">
      @else
         <input type="text" class="form-control" value="{{ $persuratan->penandatangan?->nama_lengkap ?? 'Belum ditetapkan' }}" readonly disabled>
      @endif
   </div>
</div>

<div class="form-group">
   <label>Ringkasan</label>
   <textarea name="ringkasan" class="form-control" rows="2">{{ old('ringkasan', $isEdit ? $persuratan->ringkasan : '') }}</textarea>
</div>

<div class="form-group">
   <label>Isi Surat <span class="text-danger">*</span></label>
   @if(! $isEdit)
      <button type="button" class="btn btn-sm btn-outline-secondary mb-2" id="btn-apply-template">Terapkan Template Jenis Surat</button>
   @endif
   <textarea name="isi_surat" id="isi_surat" class="form-control" rows="12">{{ old('isi_surat', $isEdit ? $persuratan->isi_surat : '') }}</textarea>
   <small class="text-muted d-block mt-1">Gunakan toolbar untuk <strong>tebal</strong>, daftar, dan tabel.</small>
</div>

<div class="form-group">
   <label>Catatan Internal</label>
   <textarea name="catatan" class="form-control" rows="2">{{ old('catatan', $isEdit ? $persuratan->catatan : '') }}</textarea>
</div>

<div class="form-group">
   <label>Lampiran (PDF/DOC/Gambar, maks. 5 file × 5MB)</label>
   <input type="file" name="lampiran[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
   @if($isEdit && !empty($persuratan->lampiran))
      <div class="mt-2">
         @foreach($persuratan->lampiran as $file)
            <div class="custom-control custom-checkbox">
               <input type="checkbox" class="custom-control-input" name="hapus_lampiran[]" value="{{ $file['path'] }}" id="hapus-{{ md5($file['path']) }}">
               <label class="custom-control-label" for="hapus-{{ md5($file['path']) }}">
                  Hapus: <a href="{{ asset('storage/'.$file['path']) }}" target="_blank">{{ $file['name'] ?? 'Lampiran' }}</a>
               </label>
            </div>
         @endforeach
      </div>
   @endif
</div>

<div class="mt-4 btn-actions">
   <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Draft' }}</button>
   <a href="{{ route('persuratan-surat-keluar.index') }}" class="btn btn-secondary">Kembali</a>
</div>

@include('admin.Kepegawaian.persuratan.surat_keluar._tinymce-isi-surat', ['isiTemplates' => $isiTemplates])
