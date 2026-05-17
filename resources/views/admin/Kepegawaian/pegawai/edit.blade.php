@extends('layouts.main')
@section('container')
@php
   $canPendidikan = auth()->user()->hasPermission('kepegawaian.pegawai');
   $tabUrl = fn (string $tab) => route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => $tab]);
@endphp
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <div>
            <h2 class="mb-1">Edit Pegawai</h2>
            <p class="text-muted mb-0">{{ $pegawai->nama_lengkap }}{{ $pegawai->nip ? ' · NIP '.$pegawai->nip : '' }}</p>
         </div>
         <div class="btn-actions">
            <a href="{{ route('pegawai.show', $pegawai) }}" class="btn btn-secondary">Detail</a>
            <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary">Kembali</a>
         </div>
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

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
   </div>
@endif

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <ul class="nav nav-pills detail-pegawai-tabs mb-4" role="tablist">
         <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'pegawai' ? 'active' : '' }}"
               href="{{ $tabUrl('pegawai') }}">Pegawai</a>
         </li>
         <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'jabatan' ? 'active' : '' }}"
               href="{{ $tabUrl('jabatan') }}">Jabatan</a>
         </li>
         @if($canPendidikan)
         <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'pendidikan' ? 'active' : '' }}"
               href="{{ $tabUrl('pendidikan') }}">Pendidikan</a>
         </li>
         <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'dokumen' ? 'active' : '' }}"
               href="{{ $tabUrl('dokumen') }}">Upload File</a>
         </li>
         @endif
      </ul>

      <div class="tab-content">
         @if($activeTab === 'pegawai')
            @include('admin.Kepegawaian.pegawai.edit._tab-pegawai')
         @elseif($activeTab === 'jabatan')
            @include('admin.Kepegawaian.pegawai.edit._tab-jabatan')
         @elseif($activeTab === 'pendidikan' && $canPendidikan)
            @include('admin.Kepegawaian.pegawai.edit._tab-pendidikan')
         @elseif($activeTab === 'dokumen' && $canPendidikan)
            @include('admin.Kepegawaian.pegawai.edit._tab-dokumen')
         @else
            @include('admin.Kepegawaian.pegawai.edit._tab-pegawai')
         @endif
      </div>
   </div>
</div>
@endsection
