@extends('layouts.main')
@section('container')
@php
   use App\Models\SuratMasuk;
   $listRoute = $listRoute ?? 'persuratan-masuk.index';
   $showContext = ($isStagePage ?? false) ? ($workflowContext ?? 'sekretariat') : 'sekretariat';
@endphp
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">{{ $pageTitle ?? 'Surat Masuk' }}</h2>
         <div class="btn-actions">
            @if($isStagePage ?? false)
               @can('viewDaftar', SuratMasuk::class)
                  <a href="{{ route('persuratan-masuk.index') }}" class="btn btn-secondary">Daftar Surat</a>
               @endcan
            @else
               @can('viewDaftar', SuratMasuk::class)
                  <a href="{{ route('persuratan-masuk.proses-sekretariat') }}" class="btn btn-outline-primary">Proses Sekretariat</a>
                  <a href="{{ route('persuratan-masuk.arsip') }}" class="btn btn-outline-dark">Arsip</a>
               @endcan
               @can('create', SuratMasuk::class)
                  <a href="{{ route('persuratan-masuk.create') }}" class="btn btn-primary">Catat Surat Masuk</a>
               @endcan
               @can('export', SuratMasuk::class)
                  <a href="{{ route('persuratan-masuk.export', request()->query()) }}" class="btn btn-outline-success">Export CSV</a>
               @endcan
            @endif
         </div>
      </div>
   </div>
</div>

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
@endif

@isset($inboxStats)
<div class="row mb-3 persuratan-inbox-stats">
   @can('viewDaftar', SuratMasuk::class)
   <div class="col-md-3 col-6 mb-2">
      <div class="card h-100 border-primary">
         <div class="card-body py-2 px-3">
            <div class="text-muted small">Antrian Sekretariat</div>
            <div class="h5 mb-0 text-primary">{{ $inboxStats['sekretariat'] ?? 0 }}</div>
         </div>
      </div>
   </div>
   @endcan
   <div class="col-md-3 col-6 mb-2">
      <div class="card h-100 border-warning">
         <div class="card-body py-2 px-3">
            <div class="text-muted small">Menunggu Kadis</div>
            <div class="h5 mb-0 text-warning">{{ $inboxStats['kadis'] ?? 0 }}</div>
         </div>
      </div>
   </div>
   <div class="col-md-3 col-6 mb-2">
      <div class="card h-100 border-info">
         <div class="card-body py-2 px-3">
            <div class="text-muted small">TL Unit (saya)</div>
            <div class="h5 mb-0 text-info">{{ $inboxStats['unit'] ?? 0 }}</div>
         </div>
      </div>
   </div>
   <div class="col-md-3 col-6 mb-2">
      <div class="card h-100 border-danger">
         <div class="card-body py-2 px-3">
            <div class="text-muted small">Disposisi terlambat</div>
            <div class="h5 mb-0 text-danger">{{ $inboxStats['overdue'] ?? 0 }}</div>
         </div>
      </div>
   </div>
</div>
@endisset

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <form method="GET" class="form-row mb-3">
         <div class="col-md-5 mb-2">
            <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari agenda, nomor, perihal, pengirim...">
         </div>
         <div class="col-md-4 mb-2">
            <select name="status" class="form-control">
               <option value="">-- Semua Status --</option>
               @foreach($statusOptions as $key => $label)
                  <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
               @endforeach
            </select>
         </div>
         <div class="col-md-3 mb-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route($listRoute) }}" class="btn btn-secondary">Reset</a>
         </div>
      </form>

      @if($list->isEmpty())
         <p class="text-muted mb-0">Belum ada data surat masuk.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>Agenda</th>
                     <th>Tgl Terima</th>
                     <th>Pengirim</th>
                     <th>Perihal</th>
                     <th>Status</th>
                     <th>Disposisi</th>
                     <th width="80">Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $item)
                  <tr class="{{ $item->hasOverdueDisposisi() ? 'table-warning' : '' }}">
                     <td>{{ $item->nomor_agenda ?: '-' }}</td>
                     <td>{{ optional($item->tanggal_terima)->format('d-m-Y') }}</td>
                     <td>{{ Str::limit($item->pengirim, 28) }}</td>
                     <td>{{ Str::limit($item->perihal, 40) }}</td>
                     <td><span class="badge badge-{{ $item->statusBadgeClass() }}">{{ $item->statusLabel() }}</span></td>
                     <td class="small">
                        @if(($item->disposisi_total_count ?? 0) > 0)
                           {{ $item->disposisi_selesai_count }}/{{ $item->disposisi_total_count }}
                        @else
                           -
                        @endif
                     </td>
                     <td>
                        <a href="{{ route('persuratan-masuk.show', array_filter(['surat_masuk' => $item, 'context' => $showContext])) }}" class="btn btn-sm btn-info">Detail</a>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
         {{ $list->links() }}
      @endif
   </div>
</div>
@endsection
