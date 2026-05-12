@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4">
         <h2 class="mb-0">Edit Operasional Per Karcis</h2>
      </div>
   </div>
</div>

@if($errors->any())
   <div class="alert alert-danger">
      <ul class="mb-0 pl-3">
         @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
         @endforeach
      </ul>
   </div>
@endif

@php
   $item = $item;
   $sisaSebelumnya = (int) $item->sisa_lembar + (int) $item->lembar_terjual;
@endphp

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <form action="{{ route('operasional-item.update', $item) }}" method="POST" enctype="multipart/form-data">
         @csrf
         @method('PUT')

         <div class="form-row">
            <div class="form-group col-md-4">
               <label>Nama Karcis</label>
               <input type="text" class="form-control" value="{{ $item->nama_karcis }}" readonly>
            </div>
            <div class="form-group col-md-2">
               <label>Harga Satuan</label>
               <input type="text" class="form-control" value="{{ 'Rp '.number_format((float) $item->harga_satuan, 0, ',', '.') }}" readonly>
            </div>
            <div class="form-group col-md-2">
               <label>Lembar</label>
               <input type="number" class="form-control" value="{{ $item->lembar }}" readonly>
            </div>
            <div class="form-group col-md-2">
               <label>Sisa Sebelumnya</label>
               <input type="number" class="form-control" value="{{ $sisaSebelumnya }}" readonly>
            </div>
            <div class="form-group col-md-2">
               <label>Lembar Terpakai</label>
               <input type="number" name="lembar_terjual" class="form-control" min="0" max="{{ $sisaSebelumnya }}" value="{{ old('lembar_terjual', $item->lembar_terjual) }}" required>
            </div>
         </div>

         <div class="form-group">
            <label>Upload Bukti Setor (opsional, kosongkan jika tidak berubah)</label>
            <input type="file" name="bukti_setor" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            @if($item->bukti_setor)
               <small class="d-block mt-1">File saat ini: <a href="{{ asset('storage/'.$item->bukti_setor) }}" target="_blank" rel="noopener">Lihat Bukti Setor</a></small>
            @endif
         </div>

         <div class="btn-actions mt-3">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('operasional.show', $item->operasional_id) }}" class="btn btn-secondary">Kembali</a>
         </div>
      </form>
   </div>
</div>
@endsection
