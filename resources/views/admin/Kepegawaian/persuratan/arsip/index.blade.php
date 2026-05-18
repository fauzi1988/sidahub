@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center">
         <h2 class="mb-0">Arsip Surat Keluar</h2>
         @if(auth()->user()?->hasPermission('kepegawaian.persuratan.surat_keluar'))
            <a href="{{ route('persuratan-surat-keluar.index') }}" class="btn btn-secondary">Surat Keluar</a>
         @endif
      </div>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <p class="text-muted">Daftar arsip surat keluar yang sudah dinomori dan dikirim. Data tercatat otomatis dari modul Surat Keluar.</p>
      <form method="GET" class="form-row mb-3">
         <div class="col-md-8 mb-2">
            <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari nomor, perihal, pengirim...">
         </div>
         <div class="col-md-4 mb-2">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('persuratan-arsip.index') }}" class="btn btn-secondary">Reset</a>
         </div>
      </form>
      @if($list->isEmpty())
         <p class="mb-0">Belum ada arsip surat keluar.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>Tanggal Terima</th>
                     <th>Nomor</th>
                     <th>Perihal</th>
                     <th>Pengirim</th>
                     <th>Ref. Surat Keluar</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $row)
                  <tr>
                     <td>{{ optional($row->tanggal_terima)->format('d-m-Y') ?: '-' }}</td>
                     <td>{{ $row->nomor_surat ?: '-' }}</td>
                     <td>{{ $row->perihal }}</td>
                     <td>{{ $row->pengirim }}</td>
                     <td>
                        @if($row->suratKeluar)
                           <a href="{{ route('persuratan-surat-keluar.show', $row->suratKeluar) }}">Lihat</a>
                        @else
                           -
                        @endif
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
