@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4">
         <h2 class="mb-0">Tambah Master TTD</h2>
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

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <form action="{{ route('manajemen-ttd.store') }}" method="POST" enctype="multipart/form-data">
         @csrf
         @include('admin.Kepegawaian.persuratan.manajemen_ttd._form', ['manajemen_ttd' => null])
      </form>
   </div>
</div>
@endsection
