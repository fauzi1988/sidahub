@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Detail Karcis</h2>
         <div class="btn-actions">
            <a href="{{ route('karcis.index') }}" class="btn btn-secondary">Kembali</a>
            <a href="{{ route('karcis.edit', $karcis) }}" class="btn btn-warning">Edit</a>
         </div>
      </div>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <div class="table-responsive">
         <table class="table table-bordered">
            <tr><th width="30%">Kode Karcis</th><td>{{ $karcis->kode_karcis }}</td></tr>
            <tr><th>Nama Karcis</th><td>{{ $karcis->nama_karcis }}</td></tr>
            <tr><th>Harga Satuan</th><td>Rp {{ number_format((float) $karcis->harga_satuan, 0, ',', '.') }}</td></tr>
         </table>
      </div>
   </div>
</div>
@endsection
