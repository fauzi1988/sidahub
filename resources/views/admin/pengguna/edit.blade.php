@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Edit akun</h2>
         <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Kembali</a>
      </div>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <form method="post" action="{{ route('pengguna.update', $user) }}">
         @method('PUT')
         @include('admin.pengguna._form', ['user' => $user])
         <button type="submit" class="btn btn-primary">Perbarui</button>
      </form>
   </div>
</div>
@endsection
