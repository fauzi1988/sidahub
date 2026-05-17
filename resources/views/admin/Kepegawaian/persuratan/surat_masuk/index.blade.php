@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center">
         <h2 class="mb-0">Surat Masuk</h2>
         <a href="{{ route('persuratan-surat-keluar.index') }}" class="btn btn-secondary">Surat Keluar</a>
      </div>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <p class="text-muted">Surat masuk otomatis tercatat saat surat keluar dinomori dan dikirim (integrasi balasan).</p>
      @if($list->isEmpty())
         <p class="mb-0">Belum ada data surat masuk.</p>
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
