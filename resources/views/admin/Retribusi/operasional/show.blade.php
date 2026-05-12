@extends('layouts.main')
@section('container')
<style>
   .status-green {
      background-color: #28a745 !important;
      color: #fff !important;
   }
   .status-red {
      background-color: #dc3545 !important;
      color: #fff !important;
   }
   .table td.status-green,
   .table td.status-red {
      color: #fff !important;
   }
</style>
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Detail Laporan Operasional</h2>
         <div class="btn-actions">
            <a href="{{ route('operasional.index', ['tahun' => $operasional->tahun]) }}" class="btn btn-secondary">Kembali</a>
         </div>
      </div>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <table class="table table-bordered mb-4">
         @php
            $petugasAktif = $operasional->petugasPeriode->firstWhere('tanggal_selesai', null);
         @endphp
         <tr><th width="30%">Tahun Operasional</th><td>{{ $operasional->tahun ?: '-' }}</td></tr>
         <tr><th width="30%">Tanggal BAST</th><td>{{ $operasional->tanggal ? $operasional->tanggal->format('d-m-Y') : '-' }}</td></tr>
         <tr><th>Nomor BAST</th><td>{{ $operasional->nomor_bast }}</td></tr>
         <tr><th>Petugas Aktif</th><td>{{ $petugasAktif->nama_petugas ?? $operasional->nama_penanggungjawab }}</td></tr>
         <tr><th>Tempat Tugas</th><td>{{ $operasional->tempat_tugas ?: ($operasional->penyerahan->pihak_kedua_tempat_tugas ?? '-') }}</td></tr>
      </table>

      <h5>Riwayat Periode Petugas</h5>
      <div class="table-responsive mb-4">
         <table class="table table-bordered table-sm">
            <thead class="thead-light">
               <tr>
                  <th width="5%">No</th>
                  <th>Nama Petugas</th>
                  <th>Tanggal Mulai</th>
                  <th>Tanggal Selesai</th>
                  <th>Status</th>
               </tr>
            </thead>
            <tbody>
               @forelse($operasional->petugasPeriode as $periode)
                  <tr>
                     <td>{{ $loop->iteration }}</td>
                     <td>{{ $periode->nama_petugas }}</td>
                     <td>{{ $periode->tanggal_mulai ? $periode->tanggal_mulai->format('d-m-Y') : '-' }}</td>
                     <td>{{ $periode->tanggal_selesai ? $periode->tanggal_selesai->format('d-m-Y') : '-' }}</td>
                     <td>
                        @if(is_null($periode->tanggal_selesai))
                           <span class="badge badge-success">Aktif</span>
                        @else
                           <span class="badge badge-secondary">Selesai</span>
                        @endif
                     </td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="5" class="text-center text-muted">Belum ada riwayat periode petugas.</td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>

      <div class="table-responsive">
         <table class="table table-bordered table-hover">
            <thead class="thead-light">
               <tr>
                  <th>No</th>
                  <th>Nomor BAST Sumber</th>
                  <th>Petugas</th>
                  <th>Tanggal Penginputan</th>
                  <th>Nama Karcis</th>
                  <th>Harga Satuan</th>
                  <th>Lembar</th>
                  <th>Total</th>
                  <th>Lembar Terpakai</th>
                  <th>Jumlah Hasil</th>
                  <th>Total Penjualan</th>
                  <th>Sisa Lembar</th>
                  <th>Bukti Setor</th>
                  <th>Aksi</th>
               </tr>
            </thead>
            <tbody>
               @foreach($operasional->items as $item)
               <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $item->source_nomor_bast ?: ($operasional->nomor_bast ?? '-') }}</td>
                  <td>{{ $item->nama_petugas ?: '-' }}</td>
                  <td>{{ $item->tanggal_laporan ? $item->tanggal_laporan->format('d-m-Y') : ($item->created_at ? $item->created_at->format('d-m-Y') : '-') }}</td>
                  <td>{{ $item->nama_karcis }}</td>
                  <td>Rp {{ number_format((float) $item->harga_satuan, 0, ',', '.') }}</td>
                  <td>{{ $item->lembar }}</td>
                  <td class="{{ (int) $item->sisa_lembar === 0 ? 'status-green' : '' }}">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
                  <td>{{ $item->lembar_terjual }}</td>
                  <td>Rp {{ number_format((float) $item->total_terjual, 0, ',', '.') }}</td>
                  <td class="{{ (int) $item->sisa_lembar === 0 ? 'status-green' : '' }}">Rp {{ number_format((float) ($item->total_penjualan ?? 0), 0, ',', '.') }}</td>
                  <td class="{{ (int) $item->sisa_lembar === 0 ? 'status-red' : '' }}">{{ $item->sisa_lembar }}</td>
                  <td>
                     @if($item->bukti_setor)
                        <a href="{{ asset('storage/'.$item->bukti_setor) }}" target="_blank" rel="noopener">Lihat</a>
                     @else
                        -
                     @endif
                  </td>
                  <td>
                     <div class="btn-actions btn-actions--compact">
                        <a href="{{ route('operasional-item.edit', $item) }}" class="btn btn-sm btn-warning" title="Edit">
                           <i class="fa fa-pencil"></i>
                        </a>
                        <form action="{{ route('operasional-item.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data karcis ini?');">
                           @csrf
                           @method('DELETE')
                           <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                              <i class="fa fa-trash"></i>
                           </button>
                        </form>
                     </div>
                  </td>
               </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   </div>
</div>
@endsection
