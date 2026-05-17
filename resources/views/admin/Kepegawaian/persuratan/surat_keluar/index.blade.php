@extends('layouts.main')
@section('container')
@php use App\Models\SuratKeluar; @endphp
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">{{ $pageTitle ?? 'Persuratan (Surat Keluar)' }}</h2>
         <div class="btn-actions">
            <a href="{{ route('persuratan-surat-keluar.index', ['inbox' => 1]) }}" class="btn btn-outline-primary">Inbox Saya</a>
            <a href="{{ route('persuratan-surat-keluar.index') }}" class="btn btn-secondary">Semua Surat</a>
            @if($flowRoles['kabid'] ?? false)
               <a href="{{ route('persuratan-surat-keluar.approve-kabid') }}" class="btn btn-warning">Approve Kabid</a>
            @endif
            @if($flowRoles['sekretariat'] ?? false)
               <a href="{{ route('persuratan-surat-keluar.approve-sekretariat') }}" class="btn btn-warning">Approve Sekretariat</a>
            @endif
            @if($flowRoles['kadis'] ?? false)
               <a href="{{ route('persuratan-surat-keluar.approve-kadis') }}" class="btn btn-warning">Approve Kadis</a>
            @endif
            @unless($isApprovalPage ?? false)
               @can('create', SuratKeluar::class)
                  <a href="{{ route('persuratan-surat-keluar.create') }}" class="btn btn-primary">Tambah Surat</a>
               @endcan
            @endunless
         </div>
      </div>
   </div>
</div>

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
@endif

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <form method="GET" class="form-row mb-3">
         <div class="col-md-5 mb-2">
            <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari nomor, perihal, tujuan...">
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
            <a href="{{ request()->url() }}" class="btn btn-secondary">Reset</a>
         </div>
      </form>

      @if($list->isEmpty())
         <p class="text-muted mb-0">Belum ada data surat keluar.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>Tanggal</th>
                     <th>Nomor</th>
                     <th>Perihal</th>
                     <th>Unit</th>
                     <th>Status</th>
                     <th width="100">Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $item)
                  <tr>
                     <td>{{ optional($item->tanggal_surat)->format('d-m-Y') }}</td>
                     <td>{{ $item->nomor_surat ?: '-' }}</td>
                     <td>{{ Str::limit($item->perihal, 40) }}</td>
                     <td>{{ Str::limit($item->unit_kerja ?? '-', 20) }}</td>
                     <td><span class="badge badge-{{ $item->statusBadgeClass() }}">{{ SuratKeluar::statusOptions()[$item->status] ?? $item->status }}</span></td>
                     <td>
                        <a href="{{ route('persuratan-surat-keluar.show', $item) }}" class="btn btn-sm btn-primary">Detail</a>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
         <div class="mt-3">{{ $list->links() }}</div>
      @endif
   </div>
</div>
@endsection
