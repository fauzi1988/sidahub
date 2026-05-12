@php
   $isEdit = isset($laporan) && $laporan !== null;
   $selectedTahun = old('tahun', $isEdit ? $laporan->tahun : $tahun);
   $selectedPetugas = old('nama_petugas', $isEdit ? $laporan->nama_petugas : '');
   $selectedTanggal = old('tanggal', $isEdit && $laporan->tanggal ? $laporan->tanggal->format('Y-m-d') : '');
   $selectedKarcis = old('nama_karcis', $isEdit ? $laporan->nama_karcis : '');
@endphp

<div class="form-row">
   <div class="form-group col-md-3">
      <label>Tahun Laporan</label>
      <input type="text" class="form-control" value="{{ $selectedTahun }}" readonly>
      <input type="hidden" name="tahun" id="tahun_laporan" value="{{ $selectedTahun }}">
   </div>
</div>

<div class="form-row">
   <div class="form-group col-md-6">
      <label>Nama Petugas <span class="text-danger">*</span></label>
      <select name="nama_petugas" id="nama_petugas" class="form-control" required>
         <option value="">-- Pilih Nama Petugas --</option>
         @foreach($petugasOptions as $opt)
            <option value="{{ $opt['nama_petugas'] }}" @selected($selectedPetugas === $opt['nama_petugas'])>{{ $opt['nama_petugas'] }}</option>
         @endforeach
      </select>
   </div>
   <div class="form-group col-md-6">
      <label>Tempat Tugas <span class="text-danger">*</span></label>
      <input type="text" name="tempat_tugas" id="tempat_tugas" class="form-control" value="{{ old('tempat_tugas', $isEdit ? $laporan->tempat_tugas : '') }}" readonly required>
   </div>
</div>

<div class="form-row">
   <div class="form-group col-md-4">
      <label>Tanggal <span class="text-danger">*</span></label>
      <select name="tanggal" id="tanggal_laporan" class="form-control" required>
         <option value="">-- Pilih Tanggal --</option>
      </select>
   </div>
   <div class="form-group col-md-4">
      <label>Nama Karcis <span class="text-danger">*</span></label>
      <select name="nama_karcis" id="nama_karcis" class="form-control" required>
         <option value="">-- Pilih Nama Karcis --</option>
      </select>
   </div>
   <div class="form-group col-md-4">
      <label>Harga Satuan <span class="text-danger">*</span></label>
      <div class="input-group">
         <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
         <input type="text" id="harga_satuan_display" class="form-control" value="{{ number_format((float) old('harga_satuan', $isEdit ? $laporan->harga_satuan : 0), 0, ',', '.') }}" readonly>
      </div>
      <input type="hidden" name="harga_satuan" id="harga_satuan" value="{{ old('harga_satuan', $isEdit ? $laporan->harga_satuan : 0) }}">
   </div>
</div>

<div class="form-row">
   <div class="form-group col-md-3">
      <label>Jumlah Karcis <span class="text-danger">*</span></label>
      <input type="number" min="0" name="jumlah_karcis" id="jumlah_karcis" class="form-control" value="{{ old('jumlah_karcis', $isEdit ? $laporan->jumlah_karcis : 0) }}" readonly required>
   </div>
   <div class="form-group col-md-3">
      <label>Lembar Terpakai <span class="text-danger">*</span></label>
      <input type="number" min="0" name="lembar_terjual" id="lembar_terjual" class="form-control" value="{{ old('lembar_terjual', $isEdit ? $laporan->lembar_terjual : 0) }}" readonly required>
   </div>
   <div class="form-group col-md-3">
      <label>Total Penjualan <span class="text-danger">*</span></label>
      <div class="input-group">
         <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
         <input type="text" id="total_penjualan_display" class="form-control" value="{{ number_format((float) old('total_penjualan', $isEdit ? $laporan->total_penjualan : 0), 0, ',', '.') }}" readonly>
      </div>
      <input type="hidden" name="total_penjualan" id="total_penjualan" value="{{ old('total_penjualan', $isEdit ? $laporan->total_penjualan : 0) }}">
   </div>
   <div class="form-group col-md-3">
      <label>Setor KADA <span class="text-danger">*</span></label>
      <div class="input-group">
         <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
         <input type="text" id="setor_kada_display" class="form-control" value="{{ number_format((float) old('setor_kada', $isEdit ? $laporan->setor_kada : 0), 0, ',', '.') }}" inputmode="numeric" required>
      </div>
      <input type="hidden" name="setor_kada" id="setor_kada" value="{{ old('setor_kada', $isEdit ? $laporan->setor_kada : 0) }}">
   </div>
</div>

<div class="form-row">
   <div class="form-group col-md-3">
      <label>Tanggal Setor</label>
      <input type="date" name="tanggal_setor" class="form-control" value="{{ old('tanggal_setor', $isEdit && $laporan->tanggal_setor ? $laporan->tanggal_setor->format('Y-m-d') : '') }}">
   </div>
   <div class="form-group col-md-3">
      <label>Minggu Ke</label>
      <select name="minggu_ke" class="form-control">
         <option value="">-- Pilih Minggu --</option>
         <option value="1" @selected((string) old('minggu_ke', $isEdit ? $laporan->minggu_ke : '') === '1')>I</option>
         <option value="2" @selected((string) old('minggu_ke', $isEdit ? $laporan->minggu_ke : '') === '2')>II</option>
         <option value="3" @selected((string) old('minggu_ke', $isEdit ? $laporan->minggu_ke : '') === '3')>III</option>
         <option value="4" @selected((string) old('minggu_ke', $isEdit ? $laporan->minggu_ke : '') === '4')>IV</option>
      </select>
   </div>
   <div class="form-group col-md-6">
      <label>Ket</label>
      <input type="text" name="ket" class="form-control" maxlength="200" value="{{ old('ket', $isEdit ? $laporan->ket : '') }}">
   </div>
</div>

<div class="form-group">
   <label>Upload Bukti Setor</label>
   <input type="file" name="bukti_setor" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
   @if($isEdit && $laporan->bukti_setor)
      <small class="d-block mt-1">File saat ini: <a href="{{ asset('storage/'.$laporan->bukti_setor) }}" target="_blank" rel="noopener">Lihat Bukti Setor</a></small>
   @endif
</div>

<div class="mt-4 btn-actions">
   <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Perbarui' : 'Simpan' }}</button>
   <a href="{{ route('laporan-mingguan.index', ['tahun' => $selectedTahun]) }}" class="btn btn-secondary">Kembali</a>
</div>

<script>
   (function () {
      const sourceItems = @json($sourceItems);
      const namaPetugasEl = document.getElementById('nama_petugas');
      const tempatTugasEl = document.getElementById('tempat_tugas');
      const tanggalEl = document.getElementById('tanggal_laporan');
      const namaKarcisEl = document.getElementById('nama_karcis');
      const hargaSatuanDisplayEl = document.getElementById('harga_satuan_display');
      const hargaSatuanEl = document.getElementById('harga_satuan');
      const jumlahKarcisEl = document.getElementById('jumlah_karcis');
      const lembarTerjualEl = document.getElementById('lembar_terjual');
      const totalPenjualanDisplayEl = document.getElementById('total_penjualan_display');
      const totalPenjualanEl = document.getElementById('total_penjualan');
      const setorKadaDisplayEl = document.getElementById('setor_kada_display');
      const setorKadaEl = document.getElementById('setor_kada');

      const selectedTanggal = @json($selectedTanggal);
      const selectedKarcis = @json($selectedKarcis);
      const tahunLaporan = Number((document.getElementById('tahun_laporan') || {}).value || 0);

      const formatIdr = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));
      const parseIdr = (value) => Number(String(value || '').replace(/[^\d]/g, '')) || 0;

      const resetNumericFields = () => {
         hargaSatuanDisplayEl.value = formatIdr(0);
         hargaSatuanEl.value = 0;
         jumlahKarcisEl.value = 0;
         lembarTerjualEl.value = 0;
         totalPenjualanDisplayEl.value = formatIdr(0);
         totalPenjualanEl.value = 0;
      };

      const setOptions = (el, options, placeholder, selectedValue = '') => {
         el.innerHTML = '';
         const defaultOption = document.createElement('option');
         defaultOption.value = '';
         defaultOption.textContent = placeholder;
         el.appendChild(defaultOption);

         options.forEach((item) => {
            const opt = document.createElement('option');
            opt.value = item.value;
            opt.textContent = item.label;
            if (selectedValue && selectedValue === item.value) {
               opt.selected = true;
            }
            el.appendChild(opt);
         });
      };

      const getFilteredByPetugas = () => {
         const namaPetugas = namaPetugasEl.value;
         if (!namaPetugas) return [];
         return sourceItems.filter((row) => row.nama_petugas === namaPetugas && Number(row.tahun || 0) === tahunLaporan);
      };

      const fillTempatTugas = () => {
         const rows = getFilteredByPetugas();
         tempatTugasEl.value = rows.length ? (rows[0].tempat_tugas || '') : '';
      };

      const fillTanggalOptions = (selected = '') => {
         const rows = getFilteredByPetugas();
         const uniqueTanggal = [...new Set(rows.map((row) => row.tanggal))].sort().reverse();
         setOptions(
            tanggalEl,
            uniqueTanggal.map((tgl) => ({ value: tgl, label: tgl })),
            '-- Pilih Tanggal --',
            selected
         );
      };

      const fillKarcisOptions = (selected = '') => {
         const namaPetugas = namaPetugasEl.value;
         const tanggal = tanggalEl.value;
         const rows = sourceItems.filter((row) =>
            row.nama_petugas === namaPetugas &&
            row.tanggal === tanggal &&
            Number(row.tahun || 0) === tahunLaporan
         );
         setOptions(
            namaKarcisEl,
            rows.map((row) => ({ value: row.nama_karcis, label: row.nama_karcis })),
            '-- Pilih Nama Karcis --',
            selected
         );
      };

      const fillDataBySelection = () => {
         const namaPetugas = namaPetugasEl.value;
         const tanggal = tanggalEl.value;
         const namaKarcis = namaKarcisEl.value;
         const row = sourceItems.find((item) =>
            item.nama_petugas === namaPetugas &&
            item.tanggal === tanggal &&
            item.nama_karcis === namaKarcis &&
            Number(item.tahun || 0) === tahunLaporan
         );

         if (!row) {
            resetNumericFields();
            return;
         }

         const hargaSatuan = row.harga_satuan || 0;
         const totalPenjualan = row.total_penjualan || 0;
         hargaSatuanDisplayEl.value = formatIdr(hargaSatuan);
         hargaSatuanEl.value = hargaSatuan;
         jumlahKarcisEl.value = row.jumlah_karcis || 0;
         lembarTerjualEl.value = row.lembar_terjual || 0;
         totalPenjualanDisplayEl.value = formatIdr(totalPenjualan);
         totalPenjualanEl.value = totalPenjualan;
      };

      namaPetugasEl.addEventListener('change', () => {
         fillTempatTugas();
         fillTanggalOptions();
         fillKarcisOptions();
         resetNumericFields();
      });

      tanggalEl.addEventListener('change', () => {
         fillKarcisOptions();
         resetNumericFields();
      });

      namaKarcisEl.addEventListener('change', fillDataBySelection);
      if (setorKadaDisplayEl && setorKadaEl) {
         setorKadaDisplayEl.addEventListener('input', () => {
            const numericValue = parseIdr(setorKadaDisplayEl.value);
            setorKadaEl.value = numericValue;
            setorKadaDisplayEl.value = numericValue ? formatIdr(numericValue) : '';
         });

         setorKadaDisplayEl.addEventListener('blur', () => {
            const numericValue = parseIdr(setorKadaDisplayEl.value);
            setorKadaEl.value = numericValue;
            setorKadaDisplayEl.value = formatIdr(numericValue);
         });
      }

      if (namaPetugasEl.value) {
         fillTempatTugas();
         fillTanggalOptions(selectedTanggal);
         fillKarcisOptions(selectedKarcis);
         fillDataBySelection();
      }
      if (setorKadaDisplayEl && setorKadaEl) {
         const numericValue = parseIdr(setorKadaDisplayEl.value || setorKadaEl.value);
         setorKadaEl.value = numericValue;
         setorKadaDisplayEl.value = formatIdr(numericValue);
      }
   })();
</script>
