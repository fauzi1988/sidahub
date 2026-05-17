@extends('layouts.main')
@section('container')
@php use App\Models\SuratKeluar; @endphp
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <div>
            <h2 class="mb-1">Detail Surat Keluar</h2>
            <span class="badge badge-{{ $persuratan->statusBadgeClass() }}">
               {{ SuratKeluar::statusOptions()[$persuratan->status] ?? $persuratan->status }}
            </span>
         </div>
         <div class="btn-actions">
            <a href="{{ route('persuratan-surat-keluar.index') }}" class="btn btn-secondary">Kembali</a>
            @can('update', $persuratan)
               <a href="{{ route('persuratan-surat-keluar.edit', $persuratan) }}" class="btn btn-warning">Edit</a>
            @endcan
            @if($persuratan->canBePrinted())
               <a href="{{ route('persuratan-surat-keluar.print', $persuratan) }}" class="btn btn-success" target="_blank">Cetak PDF</a>
            @endif
         </div>
      </div>
   </div>
</div>

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
@endif
@if($errors->any())
   <div class="alert alert-danger"><ul class="mb-0 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

@include('admin.Kepegawaian.persuratan.surat_keluar._workflow-actions')

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <div class="table-responsive">
         <table class="table table-bordered">
            <tr><th width="28%">Nomor Surat</th><td>{{ $persuratan->nomor_surat ?: '-' }}</td></tr>
            @if($persuratan->verification_code)
            <tr><th>Kode Verifikasi</th><td><code>{{ $persuratan->verification_code }}</code></td></tr>
            @endif
            <tr><th>Tanggal Surat</th><td>{{ optional($persuratan->tanggal_surat)->format('d-m-Y') }}</td></tr>
            <tr><th>Tanggal Kirim</th><td>{{ optional($persuratan->tanggal_kirim)->format('d-m-Y') ?: '-' }}</td></tr>
            <tr><th>Perihal</th><td>{{ $persuratan->perihal }}</td></tr>
            <tr><th>Tujuan</th><td>{{ $persuratan->tujuan_surat }}</td></tr>
            <tr><th>Alamat Tujuan</th><td>{{ $persuratan->alamat_tujuan ?: '-' }}</td></tr>
            <tr><th>Jenis Surat</th><td>{{ $persuratan->jenisSuratLabel() }}</td></tr>
            <tr><th>Sifat / Prioritas</th><td>{{ SuratKeluar::sifatSuratOptions()[$persuratan->sifat_surat] ?? $persuratan->sifat_surat }} / {{ SuratKeluar::prioritasOptions()[$persuratan->prioritas] ?? $persuratan->prioritas }}</td></tr>
            <tr><th>Unit Kerja</th><td>{{ $persuratan->unit_kerja ?: '-' }}</td></tr>
            <tr><th>Pengusul</th><td>{{ $persuratan->pengusul?->nama_lengkap ?: '-' }}</td></tr>
            <tr><th>Penandatangan</th><td>{{ $persuratan->penandatangan?->nama_lengkap ?: '-' }}</td></tr>
            <tr><th>TTD</th><td>{{ $persuratan->jenis_ttd ? (SuratKeluar::jenisTtdOptions()[$persuratan->jenis_ttd] ?? $persuratan->jenis_ttd).' — '.($persuratan->ttdManagement?->nama_ttd ?? '-') : '-' }}</td></tr>
            <tr><th>Ringkasan</th><td>{{ $persuratan->ringkasan ?: '-' }}</td></tr>
            <tr><th>Isi Surat</th><td>{!! nl2br(e($persuratan->isi_surat ?: '-')) !!}</td></tr>
            <tr><th>Catatan Internal</th><td>{!! nl2br(e($persuratan->catatan ?: '-')) !!}</td></tr>
            @if($persuratan->alasan_batal)
            <tr><th>Alasan Batal</th><td class="text-danger">{{ $persuratan->alasan_batal }}</td></tr>
            @endif
         </table>
      </div>

      @if(!empty($persuratan->lampiran))
      <h6 class="mt-3">Lampiran</h6>
      <ul>
         @foreach($persuratan->lampiran as $file)
            <li><a href="{{ asset('storage/'.$file['path']) }}" target="_blank">{{ $file['name'] ?? 'Unduh' }}</a></li>
         @endforeach
      </ul>
      @endif

      @if($persuratan->suratMasuk)
      <div class="alert alert-info mt-3 mb-0">
         Terhubung ke <a href="{{ route('persuratan-surat-masuk.index') }}">Surat Masuk</a>
         (ID: {{ $persuratan->suratMasuk->id_surat_masuk }}).
      </div>
      @endif

      <h5 class="mt-4">Riwayat Workflow</h5>
      <div class="table-responsive">
         <table class="table table-bordered table-sm">
            <thead class="thead-light">
               <tr><th>Waktu</th><th>Aksi</th><th>Dari</th><th>Ke</th><th>Oleh</th><th>Catatan</th></tr>
            </thead>
            <tbody>
               @forelse($persuratan->logs->sortByDesc('id') as $log)
               <tr>
                  <td>{{ $log->created_at?->format('d-m-Y H:i') }}</td>
                  <td>{{ $log->actionLabel() }}</td>
                  <td>{{ $log->from_status ? (SuratKeluar::statusOptions()[$log->from_status] ?? $log->from_status) : '-' }}</td>
                  <td>{{ $log->to_status ? (SuratKeluar::statusOptions()[$log->to_status] ?? $log->to_status) : '-' }}</td>
                  <td>{{ $log->user?->name ?: '-' }}</td>
                  <td>{{ $log->note ?: '-' }}</td>
               </tr>
               @empty
               <tr><td colspan="6" class="text-muted text-center">Belum ada riwayat.</td></tr>
               @endforelse
            </tbody>
         </table>
      </div>
   </div>
</div>
@endsection
