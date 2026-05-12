@php
   $isEdit = isset($penerimaan) && $penerimaan !== null;
   $oldItems = old('items', [['karcis_kode' => '', 'stock_masuk' => '']]);
@endphp

<div class="form-group">
   <label>Nomor BAST <span class="text-danger">*</span></label>
   <input type="text" name="nomor_bast" class="form-control" maxlength="100" value="{{ old('nomor_bast', $isEdit ? $penerimaan->nomor_bast : '') }}" placeholder="Contoh: BAST/PK/IV/2026/001" required>
</div>

@if($isEdit)
   <div class="form-group">
      <label>Nama Karcis <span class="text-danger">*</span></label>
      <input type="text" class="form-control" value="{{ $penerimaan->karcis->nama_karcis }}" readonly disabled>
      <input type="hidden" name="karcis_kode" value="{{ $penerimaan->karcis_kode }}">
   </div>

   <div class="form-group">
      <label>Harga Satuan (Rp)</label>
      <input type="text" class="form-control" value="{{ number_format((float) $penerimaan->harga_satuan, 0, ',', '.') }}" readonly>
      <small class="text-muted">Harga diambil otomatis dari master karcis.</small>
   </div>

   <div class="form-group">
      <label>Stock Masuk <span class="text-danger">*</span></label>
      <input type="number" name="stock_masuk" class="form-control" min="1" value="{{ old('stock_masuk', $penerimaan->stock_masuk) }}" required>
   </div>
@else
   <div class="form-group">
      <label>Daftar Karcis dalam BAST <span class="text-danger">*</span></label>
      <small class="text-muted d-block mb-2">Tambahkan baris karcis sesuai isi BAST (misalnya 3 karcis = 3 baris).</small>
      <div id="karcis-items-wrapper">
         @foreach($oldItems as $idx => $item)
            <div class="form-row align-items-end karcis-item mb-2">
               <div class="form-group col-md-6">
                  <label>Nama Karcis</label>
                  <select name="items[{{ $idx }}][karcis_kode]" class="form-control karcis-select" required>
                     <option value="">-- Pilih Karcis --</option>
                     @foreach($karcisOptions as $karcis)
                        <option value="{{ $karcis->kode_karcis }}" data-harga="{{ $karcis->harga_satuan }}" @selected((string) ($item['karcis_kode'] ?? '') === (string) $karcis->kode_karcis)>
                           {{ $karcis->nama_karcis }}
                        </option>
                     @endforeach
                  </select>
               </div>
               <div class="form-group col-md-2">
                  <label>Harga Satuan</label>
                  <input type="text" class="form-control harga-view" readonly>
               </div>
               <div class="form-group col-md-3">
                  <label>Stock Masuk</label>
                  <input type="number" name="items[{{ $idx }}][stock_masuk]" class="form-control" min="1" value="{{ $item['stock_masuk'] ?? '' }}" required>
               </div>
               <div class="form-group col-md-1">
                  <button type="button" class="btn btn-danger btn-block remove-item">&times;</button>
               </div>
            </div>
         @endforeach
      </div>
      <button type="button" class="btn btn-sm btn-info mt-2" id="add-karcis-item">+ Tambah Karcis</button>
   </div>
@endif

<div class="form-group">
   <label>Upload File BAST</label>
   <input type="file" name="file_bast" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
   @if($isEdit && $penerimaan->file_bast)
      <small class="text-muted d-block mt-1">
         File saat ini:
         <a href="{{ asset('storage/'.$penerimaan->file_bast) }}" target="_blank" rel="noopener">Lihat File BAST</a>
      </small>
   @else
      <small class="text-muted d-block mt-1">Format: PDF/JPG/JPEG/PNG, maksimal 5MB.</small>
   @endif
</div>

<div class="mt-4 btn-actions">
   <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Perbarui' : 'Simpan' }}</button>
   <a href="{{ route('terima-karcis.index') }}" class="btn btn-secondary">Kembali</a>
</div>

<script>
   (function () {
      const isEdit = {{ $isEdit ? 'true' : 'false' }};
      if (isEdit) return;

      const wrapper = document.getElementById('karcis-items-wrapper');
      const addButton = document.getElementById('add-karcis-item');
      if (!wrapper || !addButton) return;

      const formatIdr = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));
      const updateHargaByRow = (row) => {
         const selectEl = row.querySelector('.karcis-select');
         const hargaEl = row.querySelector('.harga-view');
         if (!selectEl || !hargaEl) return;
         const option = selectEl.options[selectEl.selectedIndex];
         const harga = option ? option.getAttribute('data-harga') : null;
         hargaEl.value = harga ? formatIdr(harga) : '-';
      };

      const reindexRows = () => {
         const rows = wrapper.querySelectorAll('.karcis-item');
         rows.forEach((row, idx) => {
            const karcisSelect = row.querySelector('.karcis-select');
            const stockInput = row.querySelector('input[type="number"]');
            if (karcisSelect) karcisSelect.name = `items[${idx}][karcis_kode]`;
            if (stockInput) stockInput.name = `items[${idx}][stock_masuk]`;
         });
      };

      wrapper.querySelectorAll('.karcis-item').forEach((row) => updateHargaByRow(row));

      wrapper.addEventListener('change', (event) => {
         const row = event.target.closest('.karcis-item');
         if (!row) return;
         if (event.target.classList.contains('karcis-select')) {
            updateHargaByRow(row);
         }
      });

      wrapper.addEventListener('click', (event) => {
         if (!event.target.classList.contains('remove-item')) return;
         const rows = wrapper.querySelectorAll('.karcis-item');
         if (rows.length <= 1) return;
         event.target.closest('.karcis-item').remove();
         reindexRows();
      });

      addButton.addEventListener('click', () => {
         const rows = wrapper.querySelectorAll('.karcis-item');
         const clone = rows[rows.length - 1].cloneNode(true);
         const selectEl = clone.querySelector('.karcis-select');
         const stockInput = clone.querySelector('input[type="number"]');
         const hargaEl = clone.querySelector('.harga-view');

         if (selectEl) selectEl.value = '';
         if (stockInput) stockInput.value = '';
         if (hargaEl) hargaEl.value = '-';

         wrapper.appendChild(clone);
         reindexRows();
      });
   })();
</script>
