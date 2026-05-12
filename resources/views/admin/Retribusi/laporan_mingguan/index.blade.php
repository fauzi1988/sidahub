@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Laporan Mingguan</h2>
         <div class="btn-actions">
            <a href="{{ route('laporan-mingguan.create', ['tahun' => $tahun]) }}" class="btn btn-primary">Tambah Laporan Mingguan</a>
         </div>
      </div>
   </div>
</div>

<div class="row">
   <div class="col-md-7 col-sm-12 mb-3">
      <form method="GET" action="{{ route('laporan-mingguan.index') }}">
         <div class="form-row">
            <div class="col-md-4">
               <label class="mb-1">Tahun Laporan</label>
               <select name="tahun" class="form-control" onchange="this.form.submit()">
                  @foreach($tahunOptions as $y)
                     <option value="{{ $y }}" @selected((int) $tahun === (int) $y)>{{ $y }}</option>
                  @endforeach
               </select>
            </div>
            <div class="col-md-4">
               <label class="mb-1">Bulan Laporan</label>
               <select name="bulan" class="form-control" onchange="this.form.submit()">
                  <option value="0" @selected((int) $bulan === 0)>Semua Bulan</option>
                  <option value="1" @selected((int) $bulan === 1)>Januari</option>
                  <option value="2" @selected((int) $bulan === 2)>Februari</option>
                  <option value="3" @selected((int) $bulan === 3)>Maret</option>
                  <option value="4" @selected((int) $bulan === 4)>April</option>
                  <option value="5" @selected((int) $bulan === 5)>Mei</option>
                  <option value="6" @selected((int) $bulan === 6)>Juni</option>
                  <option value="7" @selected((int) $bulan === 7)>Juli</option>
                  <option value="8" @selected((int) $bulan === 8)>Agustus</option>
                  <option value="9" @selected((int) $bulan === 9)>September</option>
                  <option value="10" @selected((int) $bulan === 10)>Oktober</option>
                  <option value="11" @selected((int) $bulan === 11)>November</option>
                  <option value="12" @selected((int) $bulan === 12)>Desember</option>
               </select>
            </div>
            <div class="col-md-4">
               <label class="mb-1">&nbsp;</label>
               <a href="{{ route('laporan-mingguan.print', ['tahun' => $tahun, 'bulan' => $bulan]) }}" target="_blank" rel="noopener" class="btn btn-info btn-block">
                  Cetak PDF
               </a>
            </div>
         </div>
      </form>
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
                     <th>Tahun</th>
                     <th>Tanggal</th>
                     <th>Nama Petugas</th>
                     <th>Tempat Tugas</th>
                     <th>Nama Karcis</th>
                     <th>Total Penjualan</th>
                     <th>Setor KADA</th>
                     <th>Minggu Ke</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $row)
                  <tr>
                     <td>{{ $list->firstItem() + $loop->index }}</td>
                     <td>{{ $row->tahun }}</td>
                     <td>{{ $row->tanggal ? $row->tanggal->format('d-m-Y') : '-' }}</td>
                     <td>{{ $row->nama_petugas }}</td>
                     <td>{{ $row->tempat_tugas ?: '-' }}</td>
                     <td>{{ $row->nama_karcis }}</td>
                     <td>Rp {{ number_format((float) $row->total_penjualan, 0, ',', '.') }}</td>
                     <td>Rp {{ number_format((float) $row->setor_kada, 0, ',', '.') }}</td>
                     <td>
                        @if($row->minggu_ke === 1) I
                        @elseif($row->minggu_ke === 2) II
                        @elseif($row->minggu_ke === 3) III
                        @elseif($row->minggu_ke === 4) IV
                        @else -
                        @endif
                     </td>
                     <td>
                        <div class="btn-actions btn-actions--compact">
                           <a href="{{ route('laporan-mingguan.show', ['laporan_mingguan' => $row, 'tahun' => $tahun]) }}" class="btn btn-sm btn-secondary">Detail</a>
                           <a href="{{ route('laporan-mingguan.edit', ['laporan_mingguan' => $row, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning">Edit</a>
                           <form action="{{ route('laporan-mingguan.destroy', $row) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?');">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                           </form>
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
