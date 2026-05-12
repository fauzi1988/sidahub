@extends('layouts.main')
@section('container')
@php use App\Models\SuratKeluar; @endphp
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Detail Master TTD</h2>
         <div class="btn-actions">
            <a href="{{ route('manajemen-ttd.index') }}" class="btn btn-primary">Kembali</a>
            <a href="{{ route('manajemen-ttd.edit', $manajemen_ttd) }}" class="btn btn-primary">Edit</a>
         </div>
      </div>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <div class="table-responsive">
         <table class="table table-bordered">
            <tr><th width="30%">Nama TTD</th><td>{{ $manajemen_ttd->nama_ttd }}</td></tr>
            <tr><th>Jenis TTD</th><td>{{ SuratKeluar::jenisTtdOptions()[$manajemen_ttd->jenis_ttd] ?? $manajemen_ttd->jenis_ttd }}</td></tr>
            <tr><th>Pemilik TTD</th><td>{{ $manajemen_ttd->pemilik_ttd ?: '-' }}</td></tr>
            <tr><th>Jabatan Pemilik</th><td>{{ $manajemen_ttd->jabatan_pemilik ?: '-' }}</td></tr>
            <tr><th>Status</th><td>{{ $manajemen_ttd->is_active ? 'Aktif' : 'Nonaktif' }}</td></tr>
            <tr><th>Keterangan</th><td>{{ $manajemen_ttd->keterangan ?: '-' }}</td></tr>
            <tr>
               <th>File TTD</th>
               <td>
                  @if($manajemen_ttd->file_ttd)
                     <a href="{{ asset('storage/'.$manajemen_ttd->file_ttd) }}" target="_blank">Lihat File</a>
                  @else
                     -
                  @endif
               </td>
            </tr>
         </table>
      </div>
   </div>
</div>
@endsection
