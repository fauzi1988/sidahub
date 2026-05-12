@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Daftar Penawaran Harga</h2>
         <button type="button" class="btn btn-primary" disabled>Buat Penawaran Baru</button>
      </div>
   </div>
</div>

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
@endif

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4"></div>
</div>
@endsection
