@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Detail Laporan Mingguan</h2>
         <div class="btn-actions">
            <a href="{{ route('laporan-mingguan.index', ['tahun' => $laporan->tahun]) }}" class="btn btn-secondary">Kembali</a>
            <a href="{{ route('laporan-mingguan.edit', ['laporan_mingguan' => $laporan, 'tahun' => $laporan->tahun]) }}" class="btn btn-warning">Edit</a>
         </div>
      </div>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <table class="table table-bordered">
         <tr><th width="30%">Tahun Laporan</th><td>{{ $laporan->tahun ?: '-' }}</td></tr>
         <tr><th width="30%">Tanggal</th><td>{{ $laporan->tanggal ? $laporan->tanggal->format('d-m-Y') : '-' }}</td></tr>
         <tr><th>Nama Petugas</th><td>{{ $laporan->nama_petugas }}</td></tr>
         <tr><th>Tempat Tugas</th><td>{{ $laporan->tempat_tugas ?: '-' }}</td></tr>
         <tr><th>Nama Karcis</th><td>{{ $laporan->nama_karcis }}</td></tr>
         <tr><th>Harga Satuan</th><td>Rp {{ number_format((float) $laporan->harga_satuan, 0, ',', '.') }}</td></tr>
         <tr><th>Jumlah Karcis</th><td>{{ $laporan->jumlah_karcis }}</td></tr>
         <tr><th>Lembar Terpakai</th><td>{{ $laporan->lembar_terjual }}</td></tr>
         <tr><th>Total Penjualan</th><td>Rp {{ number_format((float) $laporan->total_penjualan, 0, ',', '.') }}</td></tr>
         <tr><th>Setor KADA</th><td>Rp {{ number_format((float) $laporan->setor_kada, 0, ',', '.') }}</td></tr>
         <tr><th>Tanggal Setor</th><td>{{ $laporan->tanggal_setor ? $laporan->tanggal_setor->format('d-m-Y') : '-' }}</td></tr>
         <tr>
            <th>Minggu Ke</th>
            <td>
               @if($laporan->minggu_ke === 1) I
               @elseif($laporan->minggu_ke === 2) II
               @elseif($laporan->minggu_ke === 3) III
               @elseif($laporan->minggu_ke === 4) IV
               @else -
               @endif
            </td>
         </tr>
         <tr><th>Ket</th><td>{{ $laporan->ket ?: '-' }}</td></tr>
         <tr>
            <th>Bukti Setor</th>
            <td>
               @if($laporan->bukti_setor)
                  <a href="{{ asset('storage/'.$laporan->bukti_setor) }}" target="_blank" rel="noopener">Lihat Bukti Setor</a>
               @else
                  -
               @endif
            </td>
         </tr>
      </table>
   </div>
</div>
@endsection
