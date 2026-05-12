@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4">
         <h2 class="mb-0">Laporan Harian Operasional Karcis</h2>
      </div>
   </div>
</div>

<div class="row">
   <div class="col-md-10 col-sm-12 mb-3">
      <form method="GET" action="{{ route('laporan-harian.index') }}">
         <div class="form-row align-items-end">
            <div class="col-md-2 mb-2">
               <label class="mb-1">Tahun</label>
               <select name="tahun" class="form-control">
                  @foreach($tahunOptions as $y)
                     <option value="{{ $y }}" @selected((int) $tahun === (int) $y)>{{ $y }}</option>
                  @endforeach
               </select>
            </div>
            <div class="col-md-2 mb-2">
               <label class="mb-1">Bulan</label>
               <select name="bulan" class="form-control">
                  <option value="">Semua Bulan</option>
                  @foreach([
                     1 => 'Januari',
                     2 => 'Februari',
                     3 => 'Maret',
                     4 => 'April',
                     5 => 'Mei',
                     6 => 'Juni',
                     7 => 'Juli',
                     8 => 'Agustus',
                     9 => 'September',
                     10 => 'Oktober',
                     11 => 'November',
                     12 => 'Desember',
                  ] as $num => $label)
                     <option value="{{ $num }}" @selected((int) $bulan === (int) $num)>{{ $label }}</option>
                  @endforeach
               </select>
            </div>
            <div class="col-md-3 mb-2">
               <label class="mb-1">Tempat Tugas</label>
               <select name="tempat_tugas" class="form-control">
                  <option value="">Semua Tempat Tugas</option>
                  @foreach($tempatTugasOptions as $opt)
                     <option value="{{ $opt }}" @selected($tempatTugas === $opt)>{{ $opt }}</option>
                  @endforeach
               </select>
            </div>
            <div class="col-md-5 mb-2">
               <div class="d-flex flex-nowrap align-items-end">
                  <button type="submit" class="btn btn-primary mr-2">Terapkan Filter</button>
                  <a href="{{ route('laporan-harian.index', ['tahun' => $tahun]) }}" class="btn btn-secondary mr-2">Reset</a>
                  <a href="{{ route('laporan-harian.print', ['tahun' => $tahun, 'bulan' => $bulan, 'tempat_tugas' => $tempatTugas]) }}" target="_blank" rel="noopener" class="btn btn-info">Cetak PDF</a>
               </div>
            </div>
         </div>
      </form>
   </div>
</div>

<div class="row">
   <div class="col-md-3 col-sm-6 mb-3">
      <div class="white_shd full p-3">
         <small class="text-muted d-block">Total Item</small>
         <h4 class="mb-0">{{ number_format((int) $totalItem, 0, ',', '.') }}</h4>
      </div>
   </div>
   <div class="col-md-3 col-sm-6 mb-3">
      <div class="white_shd full p-3">
         <small class="text-muted d-block">Total Lembar</small>
         <h4 class="mb-0">{{ number_format((int) $totalLembar, 0, ',', '.') }}</h4>
      </div>
   </div>
   <div class="col-md-3 col-sm-6 mb-3">
      <div class="white_shd full p-3">
         <small class="text-muted d-block">Lembar Terpakai</small>
         <h4 class="mb-0">{{ number_format((int) $totalLembarTerjual, 0, ',', '.') }}</h4>
      </div>
   </div>
   <div class="col-md-3 col-sm-6 mb-3">
      <div class="white_shd full p-3">
         <small class="text-muted d-block">Total Nominal Penjualan</small>
         <h4 class="mb-0">Rp {{ number_format((float) $totalPenjualan, 0, ',', '.') }}</h4>
      </div>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      @if($list->isEmpty())
         <p class="text-muted mb-0">Tidak ada data laporan harian.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>No</th>
                     <th>Tanggal Penginputan</th>
                     <th>Petugas</th>
                     <th>Tempat Tugas</th>
                     <th>Nama Karcis</th>
                     <th>Harga Satuan</th>
                     <th>Lembar</th>
                     <th>Lembar Terpakai</th>
                     <th>Jumlah Hasil</th>
                     <th>Sisa Lembar</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $row)
                  <tr>
                     <td>{{ $list->firstItem() + $loop->index }}</td>
                     <td>{{ $row->tanggal_laporan ? $row->tanggal_laporan->format('d-m-Y') : ($row->created_at ? $row->created_at->format('d-m-Y') : '-') }}</td>
                     <td>{{ $row->nama_petugas ?: '-' }}</td>
                     <td>{{ optional($row->operasional)->tempat_tugas ?: '-' }}</td>
                     <td>{{ $row->nama_karcis }}</td>
                     <td>Rp {{ number_format((float) $row->harga_satuan, 0, ',', '.') }}</td>
                     <td>{{ (int) $row->lembar }}</td>
                     <td>{{ (int) $row->lembar_terjual }}</td>
                     <td>Rp {{ number_format((float) $row->total_terjual, 0, ',', '.') }}</td>
                     <td>{{ (int) $row->sisa_lembar }}</td>
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
