@php
   $isEdit = isset($pendidikan) && $pendidikan !== null;
@endphp

<div class="form-group">
   <label>Pegawai <span class="text-danger">*</span></label>
   @if($isEdit)
      <input type="text" class="form-control" value="{{ $pendidikan->pegawai->nama_lengkap }}{{ $pendidikan->pegawai->nip ? ' (NIP: '.$pendidikan->pegawai->nip.')' : '' }}" readonly disabled>
      <input type="hidden" name="id_pegawai" value="{{ $pendidikan->id_pegawai }}">
   @elseif(!empty($preselectPegawai))
      <input type="text" class="form-control" value="{{ $preselectPegawai->nama_lengkap }}{{ $preselectPegawai->nip ? ' (NIP: '.$preselectPegawai->nip.')' : '' }}" readonly disabled>
      <input type="hidden" name="id_pegawai" value="{{ old('id_pegawai', $preselectPegawai->id_pegawai) }}">
   @else
      <select name="id_pegawai" class="form-control" required>
         <option value="">-- Pilih Pegawai --</option>
         @foreach($pegawaiOptions as $pegawai)
            <option value="{{ $pegawai->id_pegawai }}" @selected((string) old('id_pegawai', $preselectId) === (string) $pegawai->id_pegawai)>
               {{ $pegawai->nama_lengkap }}{{ $pegawai->nip ? ' (NIP: '.$pegawai->nip.')' : '' }}
            </option>
         @endforeach
      </select>
   @endif
</div>

<div class="form-row">
   <div class="form-group col-md-6">
      <label>Tingkat <span class="text-danger">*</span></label>
      <select name="tingkat" class="form-control" required>
         <option value="">-- Pilih --</option>
         @foreach(['D3', 'S1', 'S2', 'S3'] as $tk)
            <option value="{{ $tk }}" @selected(old('tingkat', $isEdit ? $pendidikan->tingkat : null) === $tk)>{{ $tk }}</option>
         @endforeach
      </select>
   </div>
   <div class="form-group col-md-6">
      <label>Tahun Lulus <span class="text-danger">*</span></label>
      <input type="number" name="tahun_lulus" class="form-control" min="1950" max="{{ (int) date('Y') + 1 }}" value="{{ old('tahun_lulus', $isEdit ? $pendidikan->tahun_lulus : '') }}" required>
   </div>
</div>

<div class="form-group">
   <label>Jurusan <span class="text-danger">*</span></label>
   <input type="text" name="jurusan" class="form-control" maxlength="150" value="{{ old('jurusan', $isEdit ? $pendidikan->jurusan : '') }}" placeholder="Contoh: Teknik Sipil, Manajemen Transportasi Darat" required>
</div>

<div class="form-group">
   <label>Nama Institusi <span class="text-danger">*</span></label>
   <input type="text" name="nama_institusi" class="form-control" maxlength="200" value="{{ old('nama_institusi', $isEdit ? $pendidikan->nama_institusi : '') }}" placeholder="Universitas / Sekolah" required>
</div>

<div class="mt-4 btn-actions">
   @if($isEdit)
      <button type="submit" class="btn btn-primary">Update</button>
   @else
      <button type="submit" class="btn btn-info">Tambah Pendidikan</button>
      @if(!empty($preselectPegawai))
         <a href="{{ route('dokumen-pegawai.create', ['id_pegawai' => $preselectPegawai->id_pegawai]) }}" class="btn btn-primary">Simpan</a>
      @endif
   @endif
   <a href="{{ route('pendidikan.index') }}" class="btn btn-secondary">Kembali</a>
</div>
