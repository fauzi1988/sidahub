@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4">
         <h2 class="mb-0">Tambah Jabatan Pegawai</h2>
      </div>
   </div>
</div>

@if($errors->any())
   <div class="alert alert-danger">
      <ul class="mb-0 pl-3">
         @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
         @endforeach
      </ul>
   </div>
@endif

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
   </div>
@endif

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <form action="{{ route('jabatan-pegawai.store') }}" method="POST">
         @csrf

         <div class="form-group">
            <label>Pegawai <span class="text-danger">*</span></label>
            @if($preselectPegawai)
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
            <div class="form-group col-md-12">
               <label>Instansi <span class="text-danger">*</span></label>
               <input type="text" name="instansi" class="form-control" maxlength="200"
                      value="{{ old('instansi', 'Dinas Perhubungan') }}" required
                      placeholder="Dinas Perhubungan">
               <small class="text-muted">Standar: Dinas Perhubungan — dapat diubah sesuai penugasan.</small>
            </div>
         </div>

         <div class="form-row">
            <div class="form-group col-md-6">
               <label>Jabatan <span class="text-danger">*</span></label>
               <input type="text" name="jabatan" class="form-control" maxlength="150" value="{{ old('jabatan') }}" required>
            </div>
            <div class="form-group col-md-6">
               <label>Unit Kerja <span class="text-danger">*</span></label>
               <input type="text" name="unit_kerja" class="form-control" maxlength="150" value="{{ old('unit_kerja') }}" required>
            </div>
         </div>

         <div class="form-row">
            <div class="form-group col-md-6">
               <label>Karpeg</label>
               <input type="text" name="karpeg" class="form-control" maxlength="100" value="{{ old('karpeg') }}">
            </div>
            <div class="form-group col-md-6">
               <label>Pangkat/Gol</label>
               <input type="text" name="pangkat_golongan" class="form-control" maxlength="100" value="{{ old('pangkat_golongan') }}">
            </div>
         </div>

         <div class="form-row">
            <div class="form-group col-md-3">
               <label>Masa Kerja Gol (Tahun)</label>
               <input type="number" name="masa_kerja_gol_tahun" class="form-control" min="0" max="99" value="{{ old('masa_kerja_gol_tahun') }}">
            </div>
            <div class="form-group col-md-3">
               <label>Masa Kerja Gol (Bulan)</label>
               <input type="number" name="masa_kerja_gol_bulan" class="form-control" min="0" max="11" value="{{ old('masa_kerja_gol_bulan') }}">
            </div>
            <div class="form-group col-md-3">
               <label>Masa Kerja Sel (Tahun)</label>
               <input type="number" name="masa_kerja_sel_tahun" class="form-control" min="0" max="99" value="{{ old('masa_kerja_sel_tahun') }}">
            </div>
            <div class="form-group col-md-3">
               <label>Masa Kerja Sel (Bulan)</label>
               <input type="number" name="masa_kerja_sel_bulan" class="form-control" min="0" max="11" value="{{ old('masa_kerja_sel_bulan') }}">
            </div>
         </div>

         <div class="form-row">
            <div class="form-group col-md-3">
               <label>Pelatihan (PIM.I)</label>
               <input type="text" name="pelatihan_pim_i" class="form-control" maxlength="100" value="{{ old('pelatihan_pim_i') }}">
            </div>
            <div class="form-group col-md-3">
               <label>Pelatihan (PIM.II)</label>
               <input type="text" name="pelatihan_pim_ii" class="form-control" maxlength="100" value="{{ old('pelatihan_pim_ii') }}">
            </div>
            <div class="form-group col-md-3">
               <label>Pelatihan (PIM.III)</label>
               <input type="text" name="pelatihan_pim_iii" class="form-control" maxlength="100" value="{{ old('pelatihan_pim_iii') }}">
            </div>
            <div class="form-group col-md-3">
               <label>Pelatihan (PIM.IV)</label>
               <input type="text" name="pelatihan_pim_iv" class="form-control" maxlength="100" value="{{ old('pelatihan_pim_iv') }}">
            </div>
         </div>

         <div class="form-row">
            <div class="form-group col-md-3">
               <label>JLH.JAM</label>
               <input type="number" name="jlh_jam" class="form-control" min="0" value="{{ old('jlh_jam') }}">
            </div>
            <div class="form-group col-md-3">
               <label>TMT Berkala Terakhir</label>
               <input type="date" name="tmt_berkala_terakhir" class="form-control" value="{{ old('tmt_berkala_terakhir') }}">
            </div>
            <div class="form-group col-md-3">
               <label>TMT CPNSD</label>
               <input type="date" name="tmt_cpnsd" class="form-control" value="{{ old('tmt_cpnsd') }}">
            </div>
            <div class="form-group col-md-3">
               <label>TMT PNS</label>
               <input type="date" name="tmt_pns" class="form-control" value="{{ old('tmt_pns') }}">
            </div>
         </div>

         <div class="form-group">
            <label>Ket</label>
            <textarea name="ket" class="form-control" rows="3" maxlength="500">{{ old('ket') }}</textarea>
         </div>

         <input type="hidden" name="tmt" value="{{ old('tmt', now()->toDateString()) }}">
         <input type="hidden" name="status_jabatan" value="{{ old('status_jabatan', 'Aktif') }}">

         <div class="mt-4 btn-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('pegawai.index') }}" class="btn btn-secondary">Kembali</a>
         </div>
      </form>
   </div>
</div>
@endsection
