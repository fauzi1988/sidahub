@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Detail Penyerahan Karcis</h2>
         <div class="btn-actions">
            <a href="{{ route('penyerahan-karcis.index') }}" class="btn btn-secondary">Kembali</a>
            <a href="{{ route('penyerahan-karcis.print', $penyerahan) }}" class="btn btn-info" target="_blank" rel="noopener">Cetak PDF</a>
            <a href="{{ route('penyerahan-karcis.edit', $penyerahan) }}" class="btn btn-warning">Edit</a>
         </div>
      </div>
   </div>
</div>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <h5>Informasi BAST</h5>
      <table class="table table-bordered mb-4">
         <tr><th width="30%">Nomor BAST</th><td>{{ $penyerahan->nomor_bast }}</td></tr>
         <tr><th>Tanggal</th><td>{{ $penyerahan->tanggal ? $penyerahan->tanggal->format('d-m-Y') : '-' }}</td></tr>
         <tr>
            <th>File Surat</th>
            <td>
               @if($penyerahan->file_surat)
                  <a href="{{ asset('storage/'.$penyerahan->file_surat) }}" target="_blank" rel="noopener">Lihat File Surat</a>
               @else
                  <span class="text-muted">Tidak ada file.</span>
               @endif
            </td>
         </tr>
      </table>

      <h5>Pihak Pertama</h5>
      <table class="table table-bordered mb-4">
         <tr><th width="30%">Nama</th><td>{{ $penyerahan->pihak_pertama_nama }}</td></tr>
         <tr><th>NIP</th><td>{{ $penyerahan->pihak_pertama_nip ?: '-' }}</td></tr>
         <tr><th>Jabatan</th><td>{{ $penyerahan->pihak_pertama_jabatan ?: '-' }}</td></tr>
         <tr><th>Instansi</th><td>{{ $penyerahan->pihak_pertama_instansi ?: '-' }}</td></tr>
         <tr><th>Alamat</th><td>{{ $penyerahan->pihak_pertama_alamat ?: '-' }}</td></tr>
      </table>

      <h5>Pihak Kedua</h5>
      <table class="table table-bordered mb-4">
         <tr><th width="30%">Nama</th><td>{{ $penyerahan->pihak_kedua_nama }}</td></tr>
         <tr><th>NIP</th><td>{{ $penyerahan->pihak_kedua_nip ?: '-' }}</td></tr>
         <tr><th>Jabatan</th><td>{{ $penyerahan->pihak_kedua_jabatan ?: '-' }}</td></tr>
         <tr><th>Tempat Tugas</th><td>{{ $penyerahan->pihak_kedua_tempat_tugas ?: '-' }}</td></tr>
         <tr><th>Instansi</th><td>{{ $penyerahan->pihak_kedua_instansi ?: '-' }}</td></tr>
         <tr><th>Alamat</th><td>{{ $penyerahan->pihak_kedua_alamat ?: '-' }}</td></tr>
      </table>

      <h5>Lampiran Rincian Karcis</h5>
      <div class="table-responsive">
         <table class="table table-bordered table-hover">
            <thead class="thead-light">
               <tr>
                  <th>No</th>
                  <th>Uraian</th>
                  <th>Harga Satuan</th>
                  <th>Lembar</th>
                  <th>Total</th>
                  <th>Nomor Seri Awal</th>
                  <th>Nomor Seri Akhir</th>
                  <th>Keterangan</th>
               </tr>
            </thead>
            <tbody>
               @forelse($penyerahan->items as $item)
               <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $item->karcis->nama_karcis ?? $item->uraian }}</td>
                  <td>{{ $item->harga_satuan ? 'Rp '.number_format((float) $item->harga_satuan, 0, ',', '.') : '-' }}</td>
                  <td>{{ $item->lembar ?? '-' }}</td>
                  <td>{{ $item->total ? 'Rp '.number_format((float) $item->total, 0, ',', '.') : '-' }}</td>
                  <td>{{ $item->nomor_seri_awal }}</td>
                  <td>{{ $item->nomor_seri_akhir }}</td>
                  <td>{{ $item->keterangan ?: '-' }}</td>
               </tr>
               @empty
               <tr><td colspan="9" class="text-center text-muted">Belum ada rincian.</td></tr>
               @endforelse
            </tbody>
         </table>
      </div>
   </div>
</div>
@endsection
