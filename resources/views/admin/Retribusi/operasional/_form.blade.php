@php
   $isEdit = isset($operasional) && $operasional !== null;
   $source = $isEdit ? $operasional : $penyerahan;
   $penyerahanId = $isEdit ? $operasional->penyerahan_karcis_id : $penyerahan->id;
   $items = old('items', $isEdit ? $operasional->items->map(function ($item) {
      return [
         'karcis_kode' => $item->karcis_kode,
         'nama_karcis' => $item->nama_karcis,
         'harga_satuan' => $item->harga_satuan,
         'lembar' => $item->lembar,
         'total' => $item->total,
         'lembar_terjual' => $item->lembar_terjual,
         'total_terjual' => $item->total_terjual,
         'sisa_lembar' => $item->sisa_lembar,
      ];
   })->toArray() : $penyerahan->items->map(function ($item) {
      return [
         'karcis_kode' => $item->karcis_kode,
         'nama_karcis' => $item->karcis->nama_karcis ?? $item->uraian,
         'harga_satuan' => $item->harga_satuan ?? 0,
         'lembar' => $item->lembar ?? 0,
         'total' => $item->total ?? 0,
         'lembar_terjual' => 0,
         'total_terjual' => 0,
         'sisa_lembar' => $item->lembar ?? 0,
      ];
   })->toArray());
@endphp

<input type="hidden" name="penyerahan_karcis_id" value="{{ old('penyerahan_karcis_id', $penyerahanId) }}">

<div class="form-row">
   <div class="form-group col-md-4">
      <label>Tanggal BAST</label>
      <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', ($source->tanggal ?? null) ? $source->tanggal->format('Y-m-d') : '') }}" readonly>
   </div>
   <div class="form-group col-md-4">
      <label>Nomor BAST</label>
      <input type="text" name="nomor_bast" class="form-control" value="{{ old('nomor_bast', $source->nomor_bast ?? '') }}" readonly>
   </div>
   <div class="form-group col-md-4">
      <label>Nama Penanggungjawab</label>
      <input type="text" name="nama_penanggungjawab" class="form-control" value="{{ old('nama_penanggungjawab', $source->nama_penanggungjawab ?? $source->pihak_kedua_nama ?? '') }}" readonly>
   </div>
</div>

<div class="table-responsive">
   <table class="table table-bordered">
      <thead class="thead-light">
         <tr>
            <th>Nama Karcis</th>
            <th>Harga Satuan</th>
            <th>Lembar</th>
            <th>Total</th>
            <th>Lembar Terpakai</th>
            <th>Total Terpakai</th>
            <th>Sisa Lembar</th>
         </tr>
      </thead>
      <tbody>
         @foreach($items as $idx => $item)
         <tr>
            <td>
               <input type="hidden" name="items[{{ $idx }}][karcis_kode]" value="{{ $item['karcis_kode'] ?? '' }}">
               <input type="text" name="items[{{ $idx }}][nama_karcis]" class="form-control" value="{{ $item['nama_karcis'] ?? '' }}" readonly>
            </td>
            <td><input type="text" class="form-control harga-satuan" value="{{ 'Rp '.number_format((float) ($item['harga_satuan'] ?? 0), 0, ',', '.') }}" readonly></td>
            <td><input type="number" name="items[{{ $idx }}][lembar]" class="form-control lembar" value="{{ $item['lembar'] ?? 0 }}" readonly></td>
            <td><input type="text" class="form-control total" value="{{ 'Rp '.number_format((float) ($item['total'] ?? 0), 0, ',', '.') }}" readonly></td>
            <td><input type="number" name="items[{{ $idx }}][lembar_terjual]" class="form-control lembar-terjual" min="0" max="{{ (int) ($item['lembar'] ?? 0) }}" value="{{ $item['lembar_terjual'] ?? 0 }}" required></td>
            <td><input type="text" class="form-control total-terjual" value="{{ 'Rp '.number_format((float) ($item['total_terjual'] ?? 0), 0, ',', '.') }}" readonly></td>
            <td><input type="text" class="form-control sisa-lembar" value="{{ $item['sisa_lembar'] ?? 0 }}" readonly></td>

            <input type="hidden" name="items[{{ $idx }}][harga_satuan]" class="harga-satuan-hidden" value="{{ (float) ($item['harga_satuan'] ?? 0) }}">
         </tr>
         @endforeach
      </tbody>
   </table>
</div>

<div class="form-group">
   <label>Upload Bukti Setor {{ $isEdit ? '' : '*' }}</label>
   <input type="file" name="bukti_setor" class="form-control" accept=".pdf,.jpg,.jpeg,.png" {{ $isEdit ? '' : 'required' }}>
   @if($isEdit && $operasional->bukti_setor)
      <small class="d-block mt-1">File saat ini: <a href="{{ asset('storage/'.$operasional->bukti_setor) }}" target="_blank" rel="noopener">Lihat Bukti Setor</a></small>
   @endif
</div>

<div class="btn-actions mt-3">
   <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Perbarui' : 'Simpan' }}</button>
   <a href="{{ route('operasional.index') }}" class="btn btn-secondary">Kembali</a>
</div>

<script>
   (function () {
      const rows = document.querySelectorAll('tbody tr');
      const formatIdr = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));

      const updateRow = (row) => {
         const lembar = Number(row.querySelector('.lembar').value || 0);
         const harga = Number(row.querySelector('.harga-satuan-hidden').value || 0);
         const lembarTerjualInput = row.querySelector('.lembar-terjual');
         let lembarTerjual = Number(lembarTerjualInput.value || 0);

         if (lembarTerjual > lembar) {
            lembarTerjual = lembar;
            lembarTerjualInput.value = lembar;
         }

         const totalTerjual = lembarTerjual * harga;
         const sisaLembar = lembar - lembarTerjual;

         row.querySelector('.total-terjual').value = `Rp ${formatIdr(totalTerjual)}`;
         row.querySelector('.sisa-lembar').value = sisaLembar;
      };

      rows.forEach((row) => {
         updateRow(row);
         row.querySelector('.lembar-terjual').addEventListener('input', () => updateRow(row));
      });
   })();
</script>
