@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4">
         <h2 class="mb-0">Tambah Laporan Operasional</h2>
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

<style>
   .operasional-row .form-group {
      margin-bottom: 0;
   }
   .operasional-row label {
      white-space: nowrap;
      font-size: 12px;
      min-height: 18px;
      margin-bottom: 4px;
      display: block;
   }
   .operasional-row .form-control {
      height: 34px;
      padding: .25rem .4rem;
      font-size: .875rem;
   }
   .operasional-row input[type="file"].form-control {
      padding-top: 3px;
   }
   .bukti-inline {
      display: flex;
      align-items: center;
      gap: 6px;
   }
   .bukti-inline .form-control {
      flex: 1;
      min-width: 0;
   }
   .bukti-setor-filename {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 120px;
      margin: 0;
      display: inline-block;
   }
   .status-green {
      background-color: #28a745 !important;
      color: #fff !important;
   }
</style>

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      @php
         $petugasAktif = optional($operasionalTahun?->petugasPeriode->firstWhere('tanggal_selesai', null))->nama_petugas;
         $defaultNamaPetugas = old('nama_petugas', $petugasAktif ?? $operasionalTahun->nama_penanggungjawab ?? $penyerahan->pihak_kedua_nama);
         $defaultTempatTugas = old('tempat_tugas', $operasionalTahun->tempat_tugas ?? $penyerahan->pihak_kedua_tempat_tugas);
      @endphp
      <div class="form-row">
         <div class="form-group col-md-2">
            <label>Tahun Operasional</label>
            <input type="text" class="form-control" value="{{ $tahun }}" readonly>
         </div>
         <div class="form-group col-md-2">
            <label>Tanggal BAST</label>
            <input type="date" class="form-control" value="{{ $penyerahan->tanggal ? $penyerahan->tanggal->format('Y-m-d') : '' }}" readonly>
         </div>
         <div class="form-group col-md-2">
            <label>Nomor BAST</label>
            <input type="text" class="form-control" value="{{ $penyerahan->nomor_bast }}" readonly>
         </div>
         <div class="form-group col-md-3">
            <label>Petugas Aktif</label>
            <input type="text" class="form-control" value="{{ $defaultNamaPetugas }}" readonly>
         </div>
         <div class="form-group col-md-3">
            <label>Tempat Tugas</label>
            <input type="text" class="form-control" id="tempat_tugas_global_display" value="{{ $defaultTempatTugas ?: '-' }}" readonly>
         </div>
         <div class="form-group col-md-2">
            <label>Tanggal Laporan Harian</label>
            <input type="date" name="tanggal_laporan" form="operasional-harian-form" class="form-control" value="{{ old('tanggal_laporan', now()->format('Y-m-d')) }}" required>
         </div>
      </div>

      @php
         $savedItems = optional($operasionalTahun)->items ?? collect();
         $latestItemsByKarcis = $savedItems
            ->sortByDesc('id')
            ->unique('karcis_kode')
            ->keyBy('karcis_kode');
         $initializedByCurrentBast = $savedItems
            ->where('source_penyerahan_karcis_id', $penyerahan->id)
            ->groupBy('karcis_kode');
      @endphp

      <form id="operasional-harian-form" action="{{ route('operasional.store') }}" method="POST" enctype="multipart/form-data">
         @csrf
         <input type="hidden" name="penyerahan_karcis_id" value="{{ $penyerahan->id }}">
         <input type="hidden" name="tanggal" value="{{ $penyerahan->tanggal ? $penyerahan->tanggal->format('Y-m-d') : '' }}">
         <input type="hidden" name="tahun" value="{{ $tahun }}">
         <input type="hidden" name="nomor_bast" value="{{ $penyerahan->nomor_bast }}">
         <input type="hidden" name="nama_penanggungjawab" value="{{ $defaultNamaPetugas }}">
         <input type="hidden" name="nama_petugas" value="{{ $defaultNamaPetugas }}">
         <input type="hidden" name="tempat_tugas" value="{{ $defaultTempatTugas }}">

         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>Nama Karcis</th>
                     <th>Harga Satuan</th>
                     <th>Lembar</th>
                     <th>Total</th>
                     <th>Lembar Terpakai (Hari Ini)</th>
                     <th>Jumlah Hasil</th>
                     <th>Total Penjualan</th>
                     <th>Sisa Lembar</th>
                     <th>Bukti Setor</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($penyerahan->items as $row)
                     @php
                        $saved = $latestItemsByKarcis->get($row->karcis_kode);
                        $harga = (float) ($row->harga_satuan ?? 0);
                        $lembarAwal = (int) ($row->lembar ?? 0);
                        $sudahInisialisasiDiBastIni = $initializedByCurrentBast->has($row->karcis_kode);
                        $sisaSebelumnya = $saved ? (int) $saved->sisa_lembar : 0;
                        $sisaSaatIni = $saved
                           ? ($sudahInisialisasiDiBastIni ? $sisaSebelumnya : $sisaSebelumnya + $lembarAwal)
                           : $lembarAwal;
                        $isClosed = $sisaSaatIni <= 0;
                        $totalPenjualanSebelumnya = (float) ($saved ? ($saved->total_penjualan ?? 0) : 0);
                     @endphp
                     <tr class="operasional-row" data-index="{{ $loop->index }}">
                        <td>
                           <input type="text" class="form-control" value="{{ $row->karcis->nama_karcis ?? $row->uraian }}" readonly>
                           <input type="hidden" name="items[{{ $loop->index }}][karcis_kode]" value="{{ $row->karcis_kode }}">
                           <input type="hidden" name="items[{{ $loop->index }}][nama_karcis]" value="{{ $row->karcis->nama_karcis ?? $row->uraian }}">
                        </td>
                        <td>
                           <input type="text" class="form-control harga-satuan" value="{{ 'Rp '.number_format($harga, 0, ',', '.') }}" readonly>
                           <input type="hidden" name="items[{{ $loop->index }}][harga_satuan]" value="{{ $harga }}">
                        </td>
                        <td>
                           <input type="number" class="form-control lembar" value="{{ $lembarAwal }}" readonly>
                           <input type="hidden" name="items[{{ $loop->index }}][lembar]" value="{{ $lembarAwal }}">
                        </td>
                        <td><input type="text" class="form-control total-field {{ $isClosed ? 'status-green' : '' }}" value="{{ 'Rp '.number_format($harga * $lembarAwal, 0, ',', '.') }}" readonly></td>
                        <td>
                           <input type="number" name="items[{{ $loop->index }}][lembar_terjual]" class="form-control lembar-terjual" min="0" max="{{ $sisaSaatIni }}" value="{{ old('items.'.$loop->index.'.lembar_terjual', 0) }}" {{ $isClosed ? 'readonly' : '' }} required>
                           @error('items.'.$loop->index.'.lembar_terjual')
                              <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </td>
                        <td><input type="text" class="form-control total-terjual" value="Rp 0" readonly></td>
                        <td><input type="text" class="form-control total-penjualan {{ $isClosed ? 'status-green' : '' }}" value="{{ 'Rp '.number_format($totalPenjualanSebelumnya, 0, ',', '.') }}" readonly></td>
                        <td>
                           <input type="text" class="form-control sisa-lembar {{ $isClosed ? 'bg-danger text-white' : '' }}" value="{{ $sisaSaatIni }}" readonly>
                           <input type="hidden" name="items[{{ $loop->index }}][sisa_sebelumnya]" value="{{ $sisaSaatIni }}">
                           <input type="hidden" class="total-penjualan-sebelumnya" value="{{ $totalPenjualanSebelumnya }}">
                        </td>
                        <td>
                           <input type="file" name="items[{{ $loop->index }}][bukti_setor]" class="form-control bukti-setor-input" accept=".pdf,.jpg,.jpeg,.png" {{ $isClosed ? 'disabled' : '' }}>
                           @if($saved && $saved->bukti_setor)
                              <small class="d-block mt-1">
                                 <a href="{{ asset('storage/'.$saved->bukti_setor) }}" target="_blank" rel="noopener">File tersimpan</a>
                              </small>
                           @endif
                        </td>
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>

         <div class="d-flex align-items-center justify-content-between mt-3">
            <a href="{{ route('operasional.index', ['tahun' => $tahun]) }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">
               <i class="fa fa-save mr-1"></i> Simpan Laporan Harian
            </button>
         </div>
      </form>
   </div>
</div>

<script>
   (function () {
      const formatIdr = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));

      document.querySelectorAll('.operasional-row').forEach((card) => {
         const harga = Number((card.querySelector('input[name$="[harga_satuan]"]') || {}).value || 0);
         const sisaSebelumnya = Number((card.querySelector('input[name$="[sisa_sebelumnya]"]') || {}).value || 0);
         const lembarTerjualInput = card.querySelector('.lembar-terjual');
         const totalField = card.querySelector('.total-field');
         const totalTerjual = card.querySelector('.total-terjual');
         const totalPenjualan = card.querySelector('.total-penjualan');
         const sisaLembar = card.querySelector('.sisa-lembar');
         const totalPenjualanSebelumnya = Number((card.querySelector('.total-penjualan-sebelumnya') || {}).value || 0);
         const buktiSetorInput = card.querySelector('.bukti-setor-input');
         if (!lembarTerjualInput || !totalTerjual || !totalPenjualan || !sisaLembar) return;

         const recalc = () => {
            let terjual = Number(lembarTerjualInput.value || 0);
            if (terjual > sisaSebelumnya) {
               terjual = sisaSebelumnya;
               lembarTerjualInput.value = sisaSebelumnya;
            }
            const hasilPenjualan = terjual * harga;
            totalTerjual.value = `Rp ${formatIdr(hasilPenjualan)}`;
            totalPenjualan.value = `Rp ${formatIdr(totalPenjualanSebelumnya + hasilPenjualan)}`;
            const sisa = sisaSebelumnya - terjual;
            sisaLembar.value = sisa;

            if (sisa <= 0) {
               sisaLembar.classList.add('bg-danger', 'text-white');
               if (totalField) totalField.classList.add('status-green');
               totalPenjualan.classList.add('status-green');
            } else {
               sisaLembar.classList.remove('bg-danger', 'text-white');
               if (totalField) totalField.classList.remove('status-green');
               totalPenjualan.classList.remove('status-green');
            }

            if (sisaSebelumnya <= 0) {
               if (buktiSetorInput) {
                  buktiSetorInput.disabled = true;
                  buktiSetorInput.required = false;
               }
            } else {
               if (buktiSetorInput) {
                  buktiSetorInput.disabled = false;
                  buktiSetorInput.required = false;
               }
            }
         };

         lembarTerjualInput.addEventListener('input', recalc);
         recalc();
      });
   })();
</script>
@endsection
