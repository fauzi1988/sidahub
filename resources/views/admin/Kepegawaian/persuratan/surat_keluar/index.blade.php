@extends('layouts.main')
@section('container')
@php
   use App\Models\SuratKeluar;
   $jenisTtdOptions = SuratKeluar::jenisTtdOptions();
@endphp
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">{{ $pageTitle ?? 'Persuratan (Surat Keluar)' }}</h2>
         <div class="btn-actions">
            <a href="{{ route('persuratan-surat-keluar.index', ['inbox' => 1]) }}" class="btn btn-primary">Inbox Saya</a>
            <a href="{{ route('persuratan-surat-keluar.index') }}" class="btn btn-primary">Semua Surat</a>
            @if(($flowRoles['kabid'] ?? false))
               <a href="{{ route('persuratan-surat-keluar.approve-kabid') }}" class="btn btn-primary">Approve Kabid</a>
            @endif
            @if(($flowRoles['sekretariat'] ?? false))
               <a href="{{ route('persuratan-surat-keluar.approve-sekretariat') }}" class="btn btn-primary">Approve Sekretariat</a>
            @endif
            @if(($flowRoles['kadis'] ?? false))
               <a href="{{ route('persuratan-surat-keluar.approve-kadis') }}" class="btn btn-primary">Approve Kadis</a>
            @endif
            @unless($isApprovalPage ?? false)
               <a href="{{ route('persuratan-surat-keluar.create') }}" class="btn btn-primary">Tambah Surat</a>
            @endunless
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
      <form method="GET" class="form-row mb-3">
         <div class="col-md-5 mb-2">
            <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari nomor surat, perihal, atau tujuan...">
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
            <a href="{{ route('persuratan-surat-keluar.index') }}" class="btn btn-primary">Reset</a>
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
                     <th>Nomor Surat</th>
                     <th>Perihal</th>
                     <th>Tujuan</th>
                     <th>Status</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $item)
                  <tr>
                     <td>{{ optional($item->tanggal_surat)->format('d-m-Y') }}</td>
                     <td>{{ $item->nomor_surat ?: '-' }}</td>
                     <td>{{ $item->perihal }}</td>
                     <td>{{ $item->tujuan_surat }}</td>
                     <td>{{ SuratKeluar::statusOptions()[$item->status] ?? $item->status }}</td>
                     <td>
                        <div class="btn-actions btn-actions--compact">
                           <a href="{{ route('persuratan-surat-keluar.show', $item) }}" class="btn btn-sm btn-primary">Detail</a>
                           <a href="{{ route('persuratan-surat-keluar.edit', $item) }}" class="btn btn-sm btn-primary">Edit</a>
                           @if(($flowRoles['operator'] ?? false) && in_array($item->status, ['draft', 'revisi_substansi'], true))
                              <form action="{{ route('persuratan-surat-keluar.submit', $item) }}" method="POST" class="d-inline">
                                 @csrf
                                 <button type="submit" class="btn btn-sm btn-primary">Kirim ke Kabid</button>
                              </form>
                           @endif
                           @if(($flowRoles['kabid'] ?? false) && $item->status === 'menunggu_review_substansi')
                              <form action="{{ route('persuratan-surat-keluar.kabid-approve', $item) }}" method="POST" class="d-inline">
                                 @csrf
                                 <button type="submit" class="btn btn-sm btn-primary">Approve Kabid</button>
                              </form>
                           @endif
                           @if(($flowRoles['sekretariat'] ?? false) && $item->status === 'menunggu_verifikasi')
                              <form action="{{ route('persuratan-surat-keluar.sekretariat-forward', $item) }}" method="POST" class="d-inline">
                                 @csrf
                                 <button type="submit" class="btn btn-sm btn-primary">Teruskan ke Kadis</button>
                              </form>
                           @endif
                           @if(($flowRoles['sekretariat'] ?? false) && $item->status === 'disetujui')
                              <a href="{{ route('persuratan-surat-keluar.show', $item) }}" class="btn btn-sm btn-primary">Nomori & Kirim</a>
                           @endif
                           @if(($flowRoles['kadis'] ?? false) && $item->status === 'menunggu_ttd')
                              <form action="{{ route('persuratan-surat-keluar.kadis-sign', $item) }}" method="POST" class="d-inline">
                                 @csrf
                                 <select name="jenis_ttd" class="form-control d-inline-block mr-1" style="width:140px;" required>
                                    <option value="">Jenis TTD</option>
                                    @foreach($jenisTtdOptions as $key => $label)
                                       <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                 </select>
                                 <select name="ttd_management_id" class="form-control d-inline-block mr-1" style="width:170px;" required>
                                    <option value="">Master TTD</option>
                                    @foreach(($ttdOptions ?? collect()) as $ttd)
                                       <option value="{{ $ttd->id_ttd }}">
                                          {{ $ttd->nama_ttd }} ({{ $jenisTtdOptions[$ttd->jenis_ttd] ?? $ttd->jenis_ttd }})
                                       </option>
                                    @endforeach
                                 </select>
                                 <button type="submit" class="btn btn-sm btn-primary">Approve Kadis</button>
                              </form>
                           @endif
                           @if($item->canBePrinted())
                              <a href="{{ route('persuratan-surat-keluar.print', $item) }}" class="btn btn-sm btn-primary" target="_blank">Cetak</a>
                           @else
                              <button type="button" class="btn btn-sm btn-primary" disabled title="Surat hanya dapat dicetak setelah ditandatangani.">Cetak</button>
                           @endif
                           @php
                              $canDelete = in_array($item->status, ['draft', 'revisi_substansi', 'menunggu_review_substansi'], true);
                              $hideDeleteOnApprovalPage = in_array(($currentApprovalStage ?? ''), ['kabid', 'sekretariat'], true);
                           @endphp
                           @if(! $hideDeleteOnApprovalPage && $canDelete)
                              <form action="{{ route('persuratan-surat-keluar.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus surat ini?');">
                                 @csrf
                                 @method('DELETE')
                                 <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                              </form>
                           @elseif(! $hideDeleteOnApprovalPage)
                              <button type="button" class="btn btn-sm btn-danger" disabled title="Surat tidak bisa dihapus karena sudah berjalan setelah approval Kabid.">Hapus</button>
                           @endif
                        </div>
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
