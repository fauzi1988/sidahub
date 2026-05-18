@extends('layouts.main')
@section('container')
@php use App\Models\SuratMasuk; @endphp
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <div>
            <h2 class="mb-1">Detail Surat Masuk</h2>
            <span class="badge badge-{{ $surat->statusBadgeClass() }}">{{ $surat->statusLabel() }}</span>
            @if($surat->nomor_agenda)
               <span class="badge badge-light border ml-1">Agenda: {{ $surat->nomor_agenda }}</span>
            @endif
            @if($surat->hasOverdueDisposisi())
               <span class="badge badge-danger ml-1">Ada disposisi terlambat</span>
            @endif
         </div>
         <div class="btn-actions">
            <a href="{{ route($backRoute ?? 'persuratan-masuk.proses-sekretariat') }}" class="btn btn-secondary">Kembali</a>
            @can('update', $surat)
               <a href="{{ route('persuratan-masuk.edit', $surat) }}" class="btn btn-warning">Edit</a>
            @endcan
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

@include('admin.Kepegawaian.persuratan.surat_masuk._workflow-actions')

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <div class="table-responsive">
         <table class="table table-bordered">
            <tr><th width="28%">Nomor Agenda</th><td>{{ $surat->nomor_agenda ?: '-' }}</td></tr>
            <tr><th>Nomor Surat Pengirim</th><td>{{ $surat->nomor_surat_pengirim ?: '-' }}</td></tr>
            <tr><th>Tanggal Surat</th><td>{{ optional($surat->tanggal_surat)->format('d-m-Y') }}</td></tr>
            <tr><th>Tanggal Terima</th><td>{{ optional($surat->tanggal_terima)->format('d-m-Y') }}</td></tr>
            <tr><th>Perihal</th><td>{{ $surat->perihal }}</td></tr>
            <tr><th>Pengirim</th><td>{{ $surat->pengirim }}</td></tr>
            <tr><th>Sifat Surat</th><td>{{ SuratMasuk::sifatSuratOptions()[$surat->sifat_surat] ?? $surat->sifat_surat }}</td></tr>
            <tr><th>Ringkasan</th><td>{!! nl2br(e($surat->ringkasan ?: '-')) !!}</td></tr>
            <tr><th>Dicatat Oleh</th><td>{{ $surat->createdBy?->name ?: '-' }}</td></tr>
            <tr><th>Progress Disposisi</th><td>{{ $surat->disposisiProgress() }}</td></tr>
            <tr><th>Surat Keluar Balasan</th><td>
               @if($surat->suratKeluarBalasan)
                  <a href="{{ route('persuratan-surat-keluar.show', $surat->suratKeluarBalasan) }}">{{ $surat->suratKeluarBalasan->nomor_surat ?: 'Lihat surat keluar' }}</a>
                  — {{ $surat->suratKeluarBalasan->perihal }}
               @else
                  -
               @endif
            </td></tr>
            @if($surat->alasan_batal)
            <tr><th>Alasan Batal</th><td class="text-danger">{{ $surat->alasan_batal }}</td></tr>
            @endif
         </table>
      </div>

      @if(!empty($surat->lampiran))
      <h6 class="mt-3">Lampiran</h6>
      <ul>
         @foreach($surat->lampiran as $file)
            <li><a href="{{ asset('storage/'.$file['path']) }}" target="_blank">{{ $file['name'] ?? 'Unduh' }}</a></li>
         @endforeach
      </ul>
      @endif

      @if($surat->disposisi->isNotEmpty())
      <h5 class="mt-4">Disposisi</h5>
      <div class="table-responsive">
         <table class="table table-bordered table-sm">
            <thead class="thead-light">
               <tr>
                  <th>Target</th>
                  <th>Instruksi</th>
                  <th>Batas Waktu</th>
                  <th>Status</th>
               </tr>
            </thead>
            <tbody>
               @foreach($surat->disposisi as $d)
               <tr class="{{ $d->isOverdue() ? 'table-danger' : '' }}">
                  <td>{{ $d->targetLabel() }}</td>
                  <td>{{ $d->instruksi }}</td>
                  <td>
                     {{ optional($d->batas_waktu)->format('d-m-Y') ?: '-' }}
                     @if($d->isOverdue()) <span class="badge badge-danger">Terlambat</span> @endif
                  </td>
                  <td><span class="badge badge-{{ $d->status === 'selesai' ? 'success' : ($d->status === 'aktif' ? 'primary' : 'secondary') }}">{{ $d->statusLabel() }}</span></td>
               </tr>
               @endforeach
            </tbody>
         </table>
      </div>
      @endif

      <h5 class="mt-4">Riwayat</h5>
      <div class="table-responsive">
         <table class="table table-bordered table-sm">
            <thead class="thead-light">
               <tr><th>Waktu</th><th>Aksi</th><th>Dari</th><th>Ke</th><th>Oleh</th><th>Catatan</th></tr>
            </thead>
            <tbody>
               @forelse($surat->logs->sortByDesc('id') as $log)
               <tr>
                  <td>{{ $log->created_at?->format('d-m-Y H:i') }}</td>
                  <td>{{ $log->actionLabel() }}</td>
                  <td>{{ $log->from_status ? (SuratMasuk::statusOptions()[$log->from_status] ?? $log->from_status) : '-' }}</td>
                  <td>{{ $log->to_status ? (SuratMasuk::statusOptions()[$log->to_status] ?? $log->to_status) : '-' }}</td>
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
