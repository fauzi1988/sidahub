@extends('layouts.main')
@section('container')
@php
   use App\Models\SuratKeluar;
   $jenisTtdOptions = SuratKeluar::jenisTtdOptions();
@endphp
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Detail Surat Keluar</h2>
         <div class="btn-actions">
            <a href="{{ route('persuratan-surat-keluar.index') }}" class="btn btn-primary">Kembali</a>
            @if(($flowRoles['operator'] ?? false) && in_array($persuratan->status, ['draft', 'revisi_substansi'], true))
               <form action="{{ route('persuratan-surat-keluar.submit', $persuratan) }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-primary">Kirim ke Kabid</button>
               </form>
            @endif
            @if(($flowRoles['kabid'] ?? false) && $persuratan->status === 'menunggu_review_substansi')
               <form action="{{ route('persuratan-surat-keluar.kabid-approve', $persuratan) }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-primary">Approve Kabid</button>
               </form>
               <form action="{{ route('persuratan-surat-keluar.kabid-revisi', $persuratan) }}" method="POST" class="d-inline">
                  @csrf
                  <input type="hidden" name="note" value="Perlu perbaikan substansi oleh operator bidang.">
                  <button type="submit" class="btn btn-primary">Kembalikan Revisi</button>
               </form>
            @endif
            @if(($flowRoles['sekretariat'] ?? false) && $persuratan->status === 'menunggu_verifikasi')
               <form action="{{ route('persuratan-surat-keluar.sekretariat-forward', $persuratan) }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-primary">Teruskan ke Kadis</button>
               </form>
            @endif
            @if(($flowRoles['sekretariat'] ?? false) && $persuratan->status === 'disetujui')
               <form action="{{ route('persuratan-surat-keluar.sekretariat-number-send', $persuratan) }}" method="POST" class="d-inline">
                  @csrf
                  <input type="text" name="nomor_surat" class="form-control d-inline-block mr-2" style="width:220px;" placeholder="Nomor surat" required>
                  <button type="submit" class="btn btn-primary">Nomori & Kirim</button>
               </form>
            @endif
            @if(($flowRoles['kadis'] ?? false) && $persuratan->status === 'menunggu_ttd')
               <form action="{{ route('persuratan-surat-keluar.kadis-sign', $persuratan) }}" method="POST" class="d-inline">
                  @csrf
                  <select name="jenis_ttd" class="form-control d-inline-block mr-2" style="width:170px;" required>
                     <option value="">Pilih Jenis TTD</option>
                     @foreach($jenisTtdOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                     @endforeach
                  </select>
                  <select name="ttd_management_id" class="form-control d-inline-block mr-2" style="width:220px;" required>
                     <option value="">Pilih Master TTD</option>
                     @foreach(($ttdOptions ?? collect()) as $ttd)
                        <option value="{{ $ttd->id_ttd }}">
                           {{ $ttd->nama_ttd }} ({{ $jenisTtdOptions[$ttd->jenis_ttd] ?? $ttd->jenis_ttd }})
                        </option>
                     @endforeach
                  </select>
                  <button type="submit" class="btn btn-primary">Approve Kadis</button>
               </form>
            @endif
            @if($persuratan->canBePrinted())
               <a href="{{ route('persuratan-surat-keluar.print', $persuratan) }}" class="btn btn-primary" target="_blank">Cetak PDF</a>
            @else
               <button type="button" class="btn btn-primary" disabled title="Surat hanya dapat dicetak setelah ditandatangani.">Cetak PDF</button>
            @endif
            <a href="{{ route('persuratan-surat-keluar.edit', $persuratan) }}" class="btn btn-primary">Edit</a>
         </div>
      </div>
   </div>
</div>

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
   </div>
@endif
@if($errors->any())
   <div class="alert alert-danger">
      <ul class="mb-0 pl-3">
         @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
         @endforeach
      </ul>
   </div>
@endif

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <div class="table-responsive">
         <table class="table table-bordered">
            <tr><th width="30%">ID Surat</th><td>{{ $persuratan->id_surat_keluar }}</td></tr>
            <tr><th>Nomor Surat</th><td>{{ $persuratan->nomor_surat ?: '-' }}</td></tr>
            <tr><th>Tanggal Surat</th><td>{{ optional($persuratan->tanggal_surat)->format('d-m-Y') }}</td></tr>
            <tr><th>Tanggal Kirim</th><td>{{ optional($persuratan->tanggal_kirim)->format('d-m-Y') ?: '-' }}</td></tr>
            <tr><th>Perihal</th><td>{{ $persuratan->perihal }}</td></tr>
            <tr><th>Tujuan Surat</th><td>{{ $persuratan->tujuan_surat }}</td></tr>
            <tr><th>Alamat Tujuan</th><td>{{ $persuratan->alamat_tujuan ?: '-' }}</td></tr>
            <tr><th>Jenis Surat</th><td>{{ SuratKeluar::jenisSuratOptions()[$persuratan->jenis_surat] ?? $persuratan->jenis_surat }}</td></tr>
            <tr><th>Sifat Surat</th><td>{{ SuratKeluar::sifatSuratOptions()[$persuratan->sifat_surat] ?? $persuratan->sifat_surat }}</td></tr>
            <tr><th>Prioritas</th><td>{{ SuratKeluar::prioritasOptions()[$persuratan->prioritas] ?? $persuratan->prioritas }}</td></tr>
            <tr><th>Status</th><td>{{ SuratKeluar::statusOptions()[$persuratan->status] ?? $persuratan->status }}</td></tr>
            <tr><th>Pegawai Pengusul</th><td>{{ $persuratan->pengusul?->nama_lengkap ?: '-' }}</td></tr>
            <tr><th>Pegawai Penandatangan</th><td>{{ $persuratan->penandatangan?->nama_lengkap ?: '-' }}</td></tr>
            <tr><th>Jenis TTD</th><td>{{ $persuratan->jenis_ttd ? ($jenisTtdOptions[$persuratan->jenis_ttd] ?? $persuratan->jenis_ttd) : '-' }}</td></tr>
            <tr><th>Master TTD</th><td>{{ $persuratan->ttdManagement?->nama_ttd ?: '-' }}</td></tr>
            <tr><th>Ringkasan</th><td>{{ $persuratan->ringkasan ?: '-' }}</td></tr>
            <tr><th>Isi Surat</th><td>{!! nl2br(e($persuratan->isi_surat ?: '-')) !!}</td></tr>
            <tr><th>Catatan Internal</th><td>{!! nl2br(e($persuratan->catatan ?: '-')) !!}</td></tr>
         </table>
      </div>

      <h5 class="mt-4">Riwayat Workflow</h5>
      <div class="table-responsive">
         <table class="table table-bordered">
            <thead class="thead-light">
               <tr>
                  <th>Waktu</th>
                  <th>Aksi</th>
                  <th>Dari</th>
                  <th>Ke</th>
                  <th>Oleh</th>
                  <th>Catatan</th>
               </tr>
            </thead>
            <tbody>
               @forelse($persuratan->logs->sortByDesc('id') as $log)
                  <tr>
                     <td>{{ $log->created_at?->format('d-m-Y H:i') }}</td>
                     <td>{{ $log->action }}</td>
                     <td>{{ $log->from_status ?: '-' }}</td>
                     <td>{{ $log->to_status ?: '-' }}</td>
                     <td>{{ $log->user?->name ?: '-' }}</td>
                     <td>{{ $log->note ?: '-' }}</td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="6" class="text-center text-muted">Belum ada riwayat workflow.</td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>
   </div>
</div>
@endsection
