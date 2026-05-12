@php
   $isEdit = isset($penyerahan) && $penyerahan !== null;
   $oldItems = old('items', $isEdit ? $penyerahan->items->map(function ($item) {
      return [
         'karcis_kode' => $item->karcis_kode,
         'harga_satuan' => $item->harga_satuan,
         'lembar' => $item->lembar,
         'total' => $item->total,
         'nomor_seri_awal' => $item->nomor_seri_awal,
         'nomor_seri_akhir' => $item->nomor_seri_akhir,
         'keterangan' => $item->keterangan,
      ];
   })->toArray() : [[
      'karcis_kode' => '',
      'harga_satuan' => '',
      'lembar' => '',
      'total' => '',
      'nomor_seri_awal' => '',
      'nomor_seri_akhir' => '',
      'keterangan' => 'Buku BPKAD',
   ]]);

   $pegawaiSearchList = isset($pegawaiSearchList) ? $pegawaiSearchList : [];

   $idPihak1 = old('pihak_pertama_id_pegawai', $isEdit ? $penyerahan->pihak_pertama_id_pegawai : null);
   $idPihak2 = old('pihak_kedua_id_pegawai', $isEdit ? $penyerahan->pihak_kedua_id_pegawai : null);
   $labelPihak1 = null;
   $labelPihak2 = null;
   foreach ($pegawaiSearchList as $row) {
       if ($idPihak1 !== null && (string) ($row['id'] ?? '') === (string) $idPihak1) {
           $labelPihak1 = $row['label'] ?? null;
       }
       if ($idPihak2 !== null && (string) ($row['id'] ?? '') === (string) $idPihak2) {
           $labelPihak2 = $row['label'] ?? null;
       }
   }
@endphp

<style>
.pegawai-picker-wrap { position: relative; }
.pegawai-picker-results {
  display: none;
  position: absolute;
  left: 0;
  right: 0;
  z-index: 1050;
  max-height: 260px;
  overflow-y: auto;
  background: #fff;
  border: 1px solid #ced4da;
  border-radius: 4px;
  box-shadow: 0 4px 12px rgba(0,0,0,.12);
  margin-top: 2px;
}
.pegawai-picker-results.show { display: block; }
.pegawai-picker-results .list-group-item {
  cursor: pointer;
  padding: 0.45rem 0.75rem;
  border: none;
  border-bottom: 1px solid #eee;
}
.pegawai-picker-results .list-group-item:last-child { border-bottom: none; }
.pegawai-picker-results .list-group-item:hover { background: #e3f2fd; }
</style>

<h5 class="mb-3">Informasi BAST</h5>
<div class="form-row">
   <div class="form-group col-md-6">
      <label>Nomor BAST <span class="text-danger">*</span></label>
      <input type="text" name="nomor_bast" class="form-control" value="{{ old('nomor_bast', $isEdit ? $penyerahan->nomor_bast : '') }}" required>
   </div>
   <div class="form-group col-md-6">
      <label>Tanggal <span class="text-danger">*</span></label>
      <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $isEdit && $penyerahan->tanggal ? $penyerahan->tanggal->format('Y-m-d') : '') }}" required>
   </div>
</div>

@if(isset($pegawaiOptions) && $pegawaiOptions->isEmpty())
<div class="alert alert-warning">
   Belum ada data pegawai di sistem. Tambahkan pegawai melalui menu <strong>Kepegawaian → Data Pegawai</strong> terlebih dahulu.
</div>
@endif

<hr>
<h5 class="mb-2">Data Pihak Pertama</h5>
<p class="text-muted small mb-3">Cari dengan <strong>NIP</strong> atau <strong>nama</strong>, lalu klik salah satu hasil. Data disimpan dari modul <strong>Kepegawaian → Data Pegawai</strong> (jabatan <em>Aktif</em> diprioritaskan).</p>
<div class="form-row">
   <div class="form-group col-md-12">
      <label>Pegawai <span class="text-danger">*</span></label>
      <input type="hidden" name="pihak_pertama_id_pegawai" id="pihak_pertama_id_pegawai" value="{{ $idPihak1 }}" required>
      <div id="pihak_pertama_selected_row" class="mb-2 p-2 rounded border bg-light {{ $idPihak1 ? '' : 'd-none' }}">
         <span class="text-success font-weight-bold">Terpilih:</span>
         <span id="pihak_pertama_selected_text">{{ $labelPihak1 }}</span>
         <button type="button" class="btn btn-link btn-sm p-0 ml-2 align-baseline" id="pihak_pertama_ganti">Ganti</button>
      </div>
      <div class="pegawai-picker-wrap" id="pihak_pertama_picker_box">
         <input type="search" id="pihak_pertama_search" class="form-control" placeholder="Ketik NIP atau nama pegawai…" autocomplete="off">
         <div id="pihak_pertama_results" class="pegawai-picker-results list-group" role="listbox"></div>
      </div>
      <small class="text-muted d-block mt-1">Maks. 80 hasil ditampilkan. Kosongkan pencarian lalu ubah lewat <em>Ganti</em> jika perlu.</small>
   </div>
</div>
<div class="form-row">
   <div class="form-group col-md-6"><label>Nama (pratinjau)</label><input type="text" id="pihak_pertama_nama_preview" class="form-control bg-light" readonly tabindex="-1" value="{{ old('pihak_pertama_nama', $isEdit ? $penyerahan->pihak_pertama_nama : '') }}"></div>
   <div class="form-group col-md-6"><label>NIP (pratinjau)</label><input type="text" id="pihak_pertama_nip_preview" class="form-control bg-light" readonly tabindex="-1" value="{{ old('pihak_pertama_nip', $isEdit ? $penyerahan->pihak_pertama_nip : '') }}"></div>
</div>
<div class="form-row">
   <div class="form-group col-md-6"><label>Jabatan (pratinjau)</label><input type="text" id="pihak_pertama_jabatan_preview" class="form-control bg-light" readonly tabindex="-1" value="{{ old('pihak_pertama_jabatan', $isEdit ? $penyerahan->pihak_pertama_jabatan : '') }}"></div>
   <div class="form-group col-md-6"><label>Instansi (pratinjau)</label><input type="text" id="pihak_pertama_instansi_preview" class="form-control bg-light" readonly tabindex="-1" value="{{ old('pihak_pertama_instansi', $isEdit ? $penyerahan->pihak_pertama_instansi : '') }}"></div>
</div>
<div class="form-group"><label>Alamat (pratinjau)</label><textarea id="pihak_pertama_alamat_preview" class="form-control bg-light" rows="2" readonly tabindex="-1">{{ old('pihak_pertama_alamat', $isEdit ? $penyerahan->pihak_pertama_alamat : '') }}</textarea></div>

<hr>
<h5 class="mb-2">Data Pihak Kedua</h5>
<p class="text-muted small mb-3">Sumber data sama seperti pihak pertama. Pihak pertama dan kedua harus pegawai berbeda.</p>
<div class="form-row">
   <div class="form-group col-md-12">
      <label>Pegawai <span class="text-danger">*</span></label>
      <input type="hidden" name="pihak_kedua_id_pegawai" id="pihak_kedua_id_pegawai" value="{{ $idPihak2 }}" required>
      <div id="pihak_kedua_selected_row" class="mb-2 p-2 rounded border bg-light {{ $idPihak2 ? '' : 'd-none' }}">
         <span class="text-success font-weight-bold">Terpilih:</span>
         <span id="pihak_kedua_selected_text">{{ $labelPihak2 }}</span>
         <button type="button" class="btn btn-link btn-sm p-0 ml-2 align-baseline" id="pihak_kedua_ganti">Ganti</button>
      </div>
      <div class="pegawai-picker-wrap" id="pihak_kedua_picker_box">
         <input type="search" id="pihak_kedua_search" class="form-control" placeholder="Ketik NIP atau nama pegawai…" autocomplete="off">
         <div id="pihak_kedua_results" class="pegawai-picker-results list-group" role="listbox"></div>
      </div>
   </div>
</div>
<div class="form-row">
   <div class="form-group col-md-6"><label>Nama (pratinjau)</label><input type="text" id="pihak_kedua_nama_preview" class="form-control bg-light" readonly tabindex="-1" value="{{ old('pihak_kedua_nama', $isEdit ? $penyerahan->pihak_kedua_nama : '') }}"></div>
   <div class="form-group col-md-6"><label>NIP (pratinjau)</label><input type="text" id="pihak_kedua_nip_preview" class="form-control bg-light" readonly tabindex="-1" value="{{ old('pihak_kedua_nip', $isEdit ? $penyerahan->pihak_kedua_nip : '') }}"></div>
</div>
<div class="form-row">
   <div class="form-group col-md-6"><label>Jabatan (pratinjau)</label><input type="text" id="pihak_kedua_jabatan_preview" class="form-control bg-light" readonly tabindex="-1" value="{{ old('pihak_kedua_jabatan', $isEdit ? $penyerahan->pihak_kedua_jabatan : '') }}"></div>
   <div class="form-group col-md-6"><label>Tempat tugas / unit (pratinjau)</label><input type="text" id="pihak_kedua_tempat_tugas_preview" class="form-control bg-light" readonly tabindex="-1" value="{{ old('pihak_kedua_tempat_tugas', $isEdit ? $penyerahan->pihak_kedua_tempat_tugas : '') }}"></div>
</div>
<div class="form-row">
   <div class="form-group col-md-6"><label>Instansi (pratinjau)</label><input type="text" id="pihak_kedua_instansi_preview" class="form-control bg-light" readonly tabindex="-1" value="{{ old('pihak_kedua_instansi', $isEdit ? $penyerahan->pihak_kedua_instansi : '') }}"></div>
   <div class="form-group col-md-6"></div>
</div>
<div class="form-group"><label>Alamat (pratinjau)</label><textarea id="pihak_kedua_alamat_preview" class="form-control bg-light" rows="2" readonly tabindex="-1">{{ old('pihak_kedua_alamat', $isEdit ? $penyerahan->pihak_kedua_alamat : '') }}</textarea></div>

<hr>
<h5 class="mb-3">Mengetahui (Kepala Dinas)</h5>
<div class="form-row">
   <div class="form-group col-md-6"><label>Nama</label><input type="text" name="mengetahui_nama" class="form-control" value="{{ old('mengetahui_nama', $isEdit ? $penyerahan->mengetahui_nama : '') }}"></div>
   <div class="form-group col-md-6"><label>NIP</label><input type="text" name="mengetahui_nip" class="form-control" value="{{ old('mengetahui_nip', $isEdit ? $penyerahan->mengetahui_nip : '') }}"></div>
</div>

<hr>
<h5 class="mb-2">Lampiran Rincian Karcis</h5>
<small class="text-muted d-block mb-2">Isi per baris seperti tabel lampiran surat (uraian, banyaknya, nomor seri, ket).</small>
<div id="items-wrapper">
   @foreach($oldItems as $idx => $item)
      <div class="form-row item-row align-items-end mb-2">
         <div class="form-group col-md-3">
            <label>Uraian (Nama Karcis)</label>
            <select name="items[{{ $idx }}][karcis_kode]" class="form-control karcis-select" required>
               <option value="">-- Pilih Karcis --</option>
               @foreach($karcisOptions as $karcis)
                  <option value="{{ $karcis->kode_karcis }}" data-harga="{{ $karcis->harga_satuan }}" @selected((string) ($item['karcis_kode'] ?? '') === (string) $karcis->kode_karcis)>
                     {{ $karcis->nama_karcis }}
                  </option>
               @endforeach
            </select>
         </div>
         <div class="form-group col-md-2"><label>Harga Satuan</label><input type="text" class="form-control harga-satuan" value="{{ isset($item['harga_satuan']) && $item['harga_satuan'] !== '' ? 'Rp '.number_format((float) $item['harga_satuan'], 0, ',', '.') : '' }}" readonly></div>
         <div class="form-group col-md-1"><label>Lembar</label><input type="number" min="1" name="items[{{ $idx }}][lembar]" class="form-control lembar-input" value="{{ $item['lembar'] ?? '' }}" required></div>
         <div class="form-group col-md-1"><label>Total</label><input type="text" class="form-control total-input" value="{{ isset($item['total']) && $item['total'] !== '' ? 'Rp '.number_format((float) $item['total'], 0, ',', '.') : '' }}" readonly></div>
         <div class="form-group col-md-1"><label>Seri Awal</label><input type="text" name="items[{{ $idx }}][nomor_seri_awal]" class="form-control" value="{{ $item['nomor_seri_awal'] ?? '' }}" required></div>
         <div class="form-group col-md-1"><label>Seri Akhir</label><input type="text" name="items[{{ $idx }}][nomor_seri_akhir]" class="form-control" value="{{ $item['nomor_seri_akhir'] ?? '' }}" required></div>
         <div class="form-group col-md-1"><label>Ket</label><input type="text" name="items[{{ $idx }}][keterangan]" class="form-control" value="{{ $item['keterangan'] ?? '' }}"></div>
         <div class="form-group col-md-1"><button type="button" class="btn btn-danger btn-block remove-item">&times;</button></div>
      </div>
   @endforeach
</div>
<div class="mb-3">
   <button type="button" class="btn btn-sm btn-info mt-2" id="add-item">+ Tambah Baris Lampiran</button>
</div>

<div class="btn-actions">
   <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Perbarui' : 'Simpan' }}</button>
   <a href="{{ route('penyerahan-karcis.index') }}" class="btn btn-secondary">Kembali</a>
</div>

<script>
   (function () {
      const pegawaiMap = @json($pegawaiPayload ?? []);
      const pegawaiSearchList = @json($pegawaiSearchList ?? []);

      const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };

      function filterPegawai(q) {
         const t = (q || '').trim().toLowerCase();
         if (t.length === 0) return [];
         return pegawaiSearchList.filter(function (p) {
            const nip = (p.nip || '').toLowerCase();
            const nama = (p.nama || '').toLowerCase();
            return nip.indexOf(t) !== -1 || nama.indexOf(t) !== -1;
         }).slice(0, 80);
      }

      function fillPreview(prefix, idStr) {
         const s = pegawaiMap[String(idStr)];
         if (!s) return;
         if (prefix === 'pertama') {
            setVal('pihak_pertama_nama_preview', s.nama);
            setVal('pihak_pertama_nip_preview', s.nip);
            setVal('pihak_pertama_jabatan_preview', s.jabatan);
            setVal('pihak_pertama_instansi_preview', s.instansi);
            const ta = document.getElementById('pihak_pertama_alamat_preview');
            if (ta) ta.value = s.alamat || '';
         } else {
            setVal('pihak_kedua_nama_preview', s.nama);
            setVal('pihak_kedua_nip_preview', s.nip);
            setVal('pihak_kedua_jabatan_preview', s.jabatan);
            setVal('pihak_kedua_tempat_tugas_preview', s.tempat_tugas);
            setVal('pihak_kedua_instansi_preview', s.instansi);
            const ta = document.getElementById('pihak_kedua_alamat_preview');
            if (ta) ta.value = s.alamat || '';
         }
      }

      function clearPreview(prefix) {
         if (prefix === 'pertama') {
            setVal('pihak_pertama_nama_preview', '');
            setVal('pihak_pertama_nip_preview', '');
            setVal('pihak_pertama_jabatan_preview', '');
            setVal('pihak_pertama_instansi_preview', '');
            const ta = document.getElementById('pihak_pertama_alamat_preview');
            if (ta) ta.value = '';
         } else {
            setVal('pihak_kedua_nama_preview', '');
            setVal('pihak_kedua_nip_preview', '');
            setVal('pihak_kedua_jabatan_preview', '');
            setVal('pihak_kedua_tempat_tugas_preview', '');
            setVal('pihak_kedua_instansi_preview', '');
            const ta = document.getElementById('pihak_kedua_alamat_preview');
            if (ta) ta.value = '';
         }
      }

      function wirePegawaiPicker(prefix) {
         const hidden = document.getElementById('pihak_' + prefix + '_id_pegawai');
         const search = document.getElementById('pihak_' + prefix + '_search');
         const results = document.getElementById('pihak_' + prefix + '_results');
         const pickerBox = document.getElementById('pihak_' + prefix + '_picker_box');
         const selectedRow = document.getElementById('pihak_' + prefix + '_selected_row');
         const selectedText = document.getElementById('pihak_' + prefix + '_selected_text');
         const ganti = document.getElementById('pihak_' + prefix + '_ganti');

         function hideResults() {
            if (!results) return;
            results.classList.remove('show');
            results.innerHTML = '';
         }

         function syncChrome() {
            const has = !!(hidden && hidden.value);
            if (pickerBox) pickerBox.classList.toggle('d-none', has);
            if (selectedRow) selectedRow.classList.toggle('d-none', !has);
         }

         function renderResults(items) {
            if (!results) return;
            results.innerHTML = '';
            items.forEach(function (p) {
               const btn = document.createElement('button');
               btn.type = 'button';
               btn.className = 'list-group-item list-group-item-action text-left';
               btn.setAttribute('data-id', String(p.id));
               btn.textContent = p.label;
               btn.addEventListener('mousedown', function (e) {
                  e.preventDefault();
                  hidden.value = String(p.id);
                  selectedText.textContent = p.label;
                  search.value = '';
                  hideResults();
                  fillPreview(prefix, hidden.value);
                  syncChrome();
               });
               results.appendChild(btn);
            });
            results.classList.toggle('show', items.length > 0);
         }

         let timer;
         function scheduleRender() {
            clearTimeout(timer);
            timer = setTimeout(function () {
               renderResults(filterPegawai(search.value));
            }, 200);
         }

         if (search) {
            search.addEventListener('input', scheduleRender);
            search.addEventListener('focus', function () {
               if ((search.value || '').trim().length > 0) scheduleRender();
            });
         }

         if (ganti) {
            ganti.addEventListener('click', function () {
               hidden.value = '';
               selectedText.textContent = '';
               search.value = '';
               hideResults();
               clearPreview(prefix);
               syncChrome();
               if (search) search.focus();
            });
         }

         document.addEventListener('click', function (e) {
            const wrap = document.getElementById('pihak_' + prefix + '_picker_box');
            if (!wrap || !results || wrap.contains(e.target)) return;
            hideResults();
         });

         syncChrome();
         if (hidden && hidden.value) fillPreview(prefix, hidden.value);
      }

      wirePegawaiPicker('pertama');
      wirePegawaiPicker('kedua');

      const wrapper = document.getElementById('items-wrapper');
      const addButton = document.getElementById('add-item');
      if (!wrapper || !addButton) return;
      const formatIdr = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));

      const updateRowCalc = (row) => {
         const selectEl = row.querySelector('.karcis-select');
         const hargaInput = row.querySelector('.harga-satuan');
         const lembarInput = row.querySelector('.lembar-input');
         const totalInput = row.querySelector('.total-input');
         if (!selectEl || !hargaInput || !lembarInput || !totalInput) return;

         const selected = selectEl.options[selectEl.selectedIndex];
         const harga = Number(selected ? selected.getAttribute('data-harga') || 0 : 0);
         const lembar = Number(lembarInput.value || 0);
         hargaInput.value = harga ? `Rp ${formatIdr(harga)}` : '';
         totalInput.value = lembar > 0 && harga > 0 ? `Rp ${formatIdr(lembar * harga)}` : '';
      };

      const reindexRows = () => {
         const rows = wrapper.querySelectorAll('.item-row');
         rows.forEach((row, idx) => {
            row.querySelectorAll('input, select').forEach((input) => {
               if (!input.name) return;
               input.name = input.name.replace(/items\[\d+\]/, `items[${idx}]`);
            });
         });
      };

      wrapper.addEventListener('click', (event) => {
         if (!event.target.classList.contains('remove-item')) return;
         const rows = wrapper.querySelectorAll('.item-row');
         if (rows.length <= 1) return;
         event.target.closest('.item-row').remove();
         reindexRows();
      });

      addButton.addEventListener('click', () => {
         const rows = wrapper.querySelectorAll('.item-row');
         const clone = rows[rows.length - 1].cloneNode(true);
         clone.querySelectorAll('input').forEach((input) => { input.value = ''; });
         const selectEl = clone.querySelector('.karcis-select');
         if (selectEl) selectEl.value = '';
         wrapper.appendChild(clone);
         reindexRows();
      });

      wrapper.addEventListener('change', (event) => {
         const row = event.target.closest('.item-row');
         if (!row) return;
         if (event.target.classList.contains('karcis-select')) {
            updateRowCalc(row);
         }
      });

      wrapper.addEventListener('input', (event) => {
         const row = event.target.closest('.item-row');
         if (!row) return;
         if (event.target.classList.contains('lembar-input')) {
            updateRowCalc(row);
         }
      });

      wrapper.querySelectorAll('.item-row').forEach((row) => updateRowCalc(row));
   })();
</script>
