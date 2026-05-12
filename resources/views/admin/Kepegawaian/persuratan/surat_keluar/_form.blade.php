@php
   use App\Models\SuratKeluar;
   $isEdit = isset($persuratan) && $persuratan !== null;
   $jenisSurat = SuratKeluar::jenisSuratOptions();
   $sifatSurat = SuratKeluar::sifatSuratOptions();
   $prioritas = SuratKeluar::prioritasOptions();
   $status = SuratKeluar::statusOptions();
@endphp

<div class="form-row">
   <div class="form-group col-md-6">
      <label>Nomor Surat</label>
      <input type="text" name="nomor_surat" class="form-control" maxlength="120" value="{{ old('nomor_surat', $isEdit ? $persuratan->nomor_surat : '') }}" placeholder="Contoh: 800/123/DISHUB/2026" @if(!$isEdit) readonly @endif>
      <small class="text-muted">
         @if($isEdit)
            Nomor surat diisi Sekretariat setelah disetujui Kepala Dinas.
         @else
            Nomor surat otomatis dikosongkan saat tambah surat, akan diisi Sekretariat setelah disetujui Kepala Dinas.
         @endif
      </small>
   </div>
   <div class="form-group col-md-3">
      <label>Tanggal Surat <span class="text-danger">*</span></label>
      <input type="date" name="tanggal_surat" class="form-control" value="{{ old('tanggal_surat', $isEdit && $persuratan->tanggal_surat ? $persuratan->tanggal_surat->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
   </div>
   <div class="form-group col-md-3">
      <label>Tanggal Kirim</label>
      <input type="date" name="tanggal_kirim" class="form-control" value="{{ old('tanggal_kirim', $isEdit && $persuratan->tanggal_kirim ? $persuratan->tanggal_kirim->format('Y-m-d') : '') }}">
   </div>
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
   <div class="form-group col-md-4">
      <label>Status Workflow <span class="text-danger">*</span></label>
      <select name="status" class="form-control" required>
         @foreach($status as $key => $label)
            <option value="{{ $key }}" @selected(old('status', $isEdit ? $persuratan->status : 'draft') === $key)>{{ $label }}</option>
         @endforeach
      </select>
   </div>
</div>

<div class="form-group">
   <label>Alamat Tujuan</label>
   <textarea name="alamat_tujuan" class="form-control" rows="2">{{ old('alamat_tujuan', $isEdit ? $persuratan->alamat_tujuan : '') }}</textarea>
</div>

<div class="form-row">
   <div class="form-group col-md-4">
      <label>Jenis Surat <span class="text-danger">*</span></label>
      <select name="jenis_surat" class="form-control" required>
         @foreach($jenisSurat as $key => $label)
            <option value="{{ $key }}" @selected(old('jenis_surat', $isEdit ? $persuratan->jenis_surat : '') === $key)>{{ $label }}</option>
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
      <select name="id_pegawai_pengusul" class="form-control">
         <option value="">-- Pilih Pegawai --</option>
         @foreach($pegawaiOptions as $pegawai)
            <option value="{{ $pegawai->id_pegawai }}" @selected((string) old('id_pegawai_pengusul', $isEdit ? $persuratan->id_pegawai_pengusul : '') === (string) $pegawai->id_pegawai)>
               {{ $pegawai->nama_lengkap }}{{ $pegawai->nip ? ' (NIP: '.$pegawai->nip.')' : '' }}
            </option>
         @endforeach
      </select>
   </div>
   <div class="form-group col-md-6">
      <label>Pegawai Penandatangan</label>
      @if(! $isEdit)
         @php
            $selectedKadisId = old('id_pegawai_penandatangan', isset($kadisPegawai) && $kadisPegawai ? $kadisPegawai->id_pegawai : '');
            $selectedKadisLabel = isset($kadisPegawai) && $kadisPegawai
               ? $kadisPegawai->nama_lengkap.($kadisPegawai->nip ? ' (NIP: '.$kadisPegawai->nip.')' : '')
               : 'Kepala Dinas belum tersedia di data jabatan aktif';
         @endphp
         <input type="text" class="form-control" value="{{ $selectedKadisLabel }}" readonly>
         <input type="hidden" name="id_pegawai_penandatangan" value="{{ $selectedKadisId }}">
         <small class="text-muted">Otomatis dari pegawai dengan jabatan Kepala Dinas.</small>
      @else
         <select name="id_pegawai_penandatangan" class="form-control">
            <option value="">-- Pilih Pegawai --</option>
            @foreach($pegawaiOptions as $pegawai)
               <option value="{{ $pegawai->id_pegawai }}" @selected((string) old('id_pegawai_penandatangan', $isEdit ? $persuratan->id_pegawai_penandatangan : '') === (string) $pegawai->id_pegawai)>
                  {{ $pegawai->nama_lengkap }}{{ $pegawai->nip ? ' (NIP: '.$pegawai->nip.')' : '' }}
               </option>
            @endforeach
         </select>
      @endif
   </div>
</div>

<div class="form-group">
   <label>Ringkasan</label>
   <textarea name="ringkasan" class="form-control" rows="2">{{ old('ringkasan', $isEdit ? $persuratan->ringkasan : '') }}</textarea>
</div>

<div class="form-group">
   <label>Isi Surat</label>
   <textarea name="isi_surat" class="form-control" rows="6">{{ old('isi_surat', $isEdit ? $persuratan->isi_surat : '') }}</textarea>
</div>

<div class="form-group">
   <label>Catatan Internal</label>
   <textarea name="catatan" class="form-control" rows="3">{{ old('catatan', $isEdit ? $persuratan->catatan : '') }}</textarea>
</div>

<div class="mt-4 btn-actions">
   <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Simpan' }}</button>
   <a href="{{ route('persuratan-surat-keluar.index') }}" class="btn btn-primary">Kembali</a>
</div>
