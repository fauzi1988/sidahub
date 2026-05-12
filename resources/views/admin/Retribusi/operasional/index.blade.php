@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4">
         <h2 class="mb-0">Operasional Karcis</h2>
      </div>
   </div>
</div>

<div class="row">
   <div class="col-md-8 col-sm-12 mb-3">
      <form method="GET" action="{{ route('operasional.index') }}">
         <div class="form-row align-items-end">
            <div class="col-md-4 mb-2">
               <label class="mb-1">Tahun Operasional</label>
               <select name="tahun" class="form-control">
                  @foreach($tahunOptions as $y)
                     <option value="{{ $y }}" @selected((int) $tahun === (int) $y)>{{ $y }}</option>
                  @endforeach
               </select>
            </div>
            <div class="col-md-4 mb-2">
               <label class="mb-1">Tanggal Laporan Harian</label>
               <input type="date" name="tanggal_laporan" class="form-control" value="{{ $tanggalLaporan }}">
            </div>
            <div class="col-md-4 mb-2">
               <button type="submit" class="btn btn-primary">Terapkan Filter</button>
               <a href="{{ route('operasional.index', ['tahun' => $tahun]) }}" class="btn btn-secondary">Reset</a>
            </div>
         </div>
      </form>
   </div>
</div>

<div class="row">
   <div class="col-md-3 col-sm-6 mb-3">
      <div class="white_shd full p-3">
         <small class="text-muted d-block">Jumlah BAST Ditemukan</small>
         <h4 class="mb-0">{{ number_format((int) $totalBastDitemukan, 0, ',', '.') }}</h4>
      </div>
   </div>
   <div class="col-md-3 col-sm-6 mb-3">
      <div class="white_shd full p-3">
         <small class="text-muted d-block">Total Item Transaksi</small>
         <h4 class="mb-0">{{ number_format((int) $totalTransaksiItem, 0, ',', '.') }}</h4>
      </div>
   </div>
   <div class="col-md-3 col-sm-6 mb-3">
      <div class="white_shd full p-3">
         <small class="text-muted d-block">Total Lembar Terpakai</small>
         <h4 class="mb-0">{{ number_format((int) $totalLembarTerjual, 0, ',', '.') }}</h4>
      </div>
   </div>
   <div class="col-md-3 col-sm-6 mb-3">
      <div class="white_shd full p-3">
         <small class="text-muted d-block">Total Nominal Penjualan</small>
         <h4 class="mb-0">Rp {{ number_format((float) $totalNominalPenjualan, 0, ',', '.') }}</h4>
      </div>
   </div>
</div>

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
   </div>
@endif

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      @if($list->isEmpty())
         <p class="text-muted mb-0">Tidak ada data BAST.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>No</th>
                     <th>Nomor BAST</th>
                     <th>Tanggal BAST</th>
                     <th>Penanggung Jawab</th>
                     <th>Tempat Tugas</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $row)
                  @php
                     $groupKey = mb_strtolower(trim((string) ($row->pihak_kedua_tempat_tugas ?? ''))).'|'.$tahun;
                     $operasionalTahun = $operasionalGrouped[$groupKey] ?? null;
                  @endphp
                  <tr>
                     <td>{{ $list->firstItem() + $loop->index }}</td>
                     <td>{{ $row->nomor_bast }}</td>
                     <td>{{ $row->tanggal ? $row->tanggal->format('d-m-Y') : '-' }}</td>
                     <td>{{ $row->pihak_kedua_nama }}</td>
                     <td>{{ $row->pihak_kedua_tempat_tugas ?: '-' }}</td>
                     <td>
                        <div class="btn-actions btn-actions--compact">
                           <a href="{{ route('operasional.create', ['penyerahan' => $row->id, 'tahun' => $tahun]) }}" class="btn btn-sm btn-primary">{{ $operasionalTahun ? 'Lanjut Laporan' : 'Laporan' }}</a>
                           @if($operasionalTahun)
                              <a href="{{ route('operasional.show', $operasionalTahun) }}" class="btn btn-sm btn-secondary">Detail</a>
                           @endif
                        </div>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
         <div class="mt-3">{{ $list->links() }}</div>
      @endif
   </div>
</div>
@endsection
