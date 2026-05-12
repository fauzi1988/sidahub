@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Detail Penerimaan Karcis</h2>
         <div class="btn-actions">
            <a href="{{ route('terima-karcis.index') }}" class="btn btn-secondary">Kembali</a>
            <a href="{{ route('terima-karcis.edit', $penerimaan) }}" class="btn btn-warning">Edit</a>
         </div>
      </div>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <div class="table-responsive">
         <table class="table table-bordered">
            <tr><th width="30%">ID</th><td>{{ $penerimaan->id }}</td></tr>
            <tr><th>Nomor BAST</th><td>{{ $penerimaan->nomor_bast }}</td></tr>
            <tr><th>Nama Karcis</th><td>{{ $penerimaan->karcis->nama_karcis }}</td></tr>
            <tr><th>Harga Satuan</th><td>Rp {{ number_format((float) $penerimaan->harga_satuan, 0, ',', '.') }}</td></tr>
            <tr><th>Stock Masuk</th><td>{{ number_format((int) $penerimaan->stock_masuk, 0, ',', '.') }}</td></tr>
            <tr><th>Total Stock</th><td>{{ number_format((int) $penerimaan->total_stock, 0, ',', '.') }}</td></tr>
            <tr><th>Total Harga</th><td>Rp {{ number_format((float) $penerimaan->total_harga, 0, ',', '.') }}</td></tr>
            <tr>
               <th>File BAST</th>
               <td>
                  @if($penerimaan->file_bast)
                     <a href="{{ asset('storage/'.$penerimaan->file_bast) }}" target="_blank" rel="noopener">Lihat File BAST</a>
                  @else
                     <span class="text-muted">Belum ada file.</span>
                  @endif
               </td>
            </tr>
         </table>
      </div>
   </div>
</div>
@endsection
