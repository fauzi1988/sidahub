@php
   use App\Models\SuratKeluar;
   $jenisTtdOptions = SuratKeluar::jenisTtdOptions();
   $s = $persuratan->status;
@endphp

<div class="workflow-actions card border mb-4">
   <div class="card-body">
      <h6 class="card-title mb-3">Aksi Workflow</h6>

      @if(($flowRoles['operator'] ?? false) && in_array($s, ['draft', 'revisi_substansi', 'revisi_admin'], true))
         <form action="{{ route('persuratan-surat-keluar.submit', $persuratan) }}" method="POST" class="mb-3">
            @csrf
            <div class="form-group">
               <label>Catatan (opsional)</label>
               <textarea name="note" class="form-control" rows="2" maxlength="2000">{{ old('note') }}</textarea>
            </div>
            <button type="submit" class="btn btn-success">Kirim ke Kabid</button>
         </form>
      @endif

      @if(($flowRoles['kabid'] ?? false) && $s === 'menunggu_review_substansi')
         <form action="{{ route('persuratan-surat-keluar.kabid-approve', $persuratan) }}" method="POST" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-success">Setujui & Teruskan ke Sekretariat</button>
         </form>
         <form action="{{ route('persuratan-surat-keluar.kabid-revisi', $persuratan) }}" method="POST" class="mb-3">
            @csrf
            <div class="form-group">
               <label>Catatan revisi <span class="text-danger">*</span></label>
               <textarea name="note" class="form-control" rows="3" required maxlength="2000">{{ old('note') }}</textarea>
            </div>
            <button type="submit" class="btn btn-warning">Kembalikan untuk Revisi Substansi</button>
         </form>
      @endif

      @if(($flowRoles['sekretariat'] ?? false) && $s === 'menunggu_verifikasi')
         <form action="{{ route('persuratan-surat-keluar.sekretariat-forward', $persuratan) }}" method="POST" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-success">Verifikasi & Teruskan ke Kadis</button>
         </form>
         <form action="{{ route('persuratan-surat-keluar.sekretariat-revisi', $persuratan) }}" method="POST" class="mb-3">
            @csrf
            <div class="form-group">
               <label>Catatan revisi administrasi <span class="text-danger">*</span></label>
               <textarea name="note" class="form-control" rows="3" required maxlength="2000">{{ old('note') }}</textarea>
            </div>
            <button type="submit" class="btn btn-warning">Kembalikan Revisi Administrasi</button>
         </form>
      @endif

      @if(($flowRoles['kadis'] ?? false) && $s === 'menunggu_ttd')
         <form action="{{ route('persuratan-surat-keluar.kadis-sign', $persuratan) }}" method="POST" class="mb-3">
            @csrf
            <div class="form-row">
               <div class="form-group col-md-4">
                  <label>Jenis TTD <span class="text-danger">*</span></label>
                  <select name="jenis_ttd" class="form-control" required>
                     <option value="">-- Pilih --</option>
                     @foreach($jenisTtdOptions as $key => $label)
                        <option value="{{ $key }}" @selected(old('jenis_ttd') === $key)>{{ $label }}</option>
                     @endforeach
                  </select>
               </div>
               <div class="form-group col-md-8">
                  <label>Master TTD <span class="text-danger">*</span></label>
                  <select name="ttd_management_id" class="form-control" required>
                     <option value="">-- Pilih Master TTD --</option>
                     @foreach(($ttdOptions ?? collect()) as $ttd)
                        <option value="{{ $ttd->id_ttd }}" @selected((string) old('ttd_management_id') === (string) $ttd->id_ttd)>
                           {{ $ttd->nama_ttd }} ({{ $jenisTtdOptions[$ttd->jenis_ttd] ?? $ttd->jenis_ttd }})
                        </option>
                     @endforeach
                  </select>
               </div>
            </div>
            <div class="form-group">
               <label>Catatan (opsional)</label>
               <textarea name="note" class="form-control" rows="2" maxlength="2000">{{ old('note') }}</textarea>
            </div>
            <button type="submit" class="btn btn-success">Tandatangani Surat</button>
         </form>
         <form action="{{ route('persuratan-surat-keluar.kadis-revisi', $persuratan) }}" method="POST" class="mb-3">
            @csrf
            <div class="form-group">
               <label>Catatan revisi <span class="text-danger">*</span></label>
               <textarea name="note" class="form-control" rows="3" required maxlength="2000">{{ old('note') }}</textarea>
            </div>
            <button type="submit" class="btn btn-warning">Kembalikan ke Sekretariat</button>
         </form>
      @endif

      @if(($flowRoles['sekretariat'] ?? false) && $s === 'disetujui')
         <form action="{{ route('persuratan-surat-keluar.sekretariat-number-send', $persuratan) }}" method="POST" class="mb-3">
            @csrf
            <div class="form-row">
               <div class="form-group col-md-6">
                  <label>Nomor Surat <span class="text-danger">*</span></label>
                  <input type="text" name="nomor_surat" id="nomor_surat_input" class="form-control"
                         value="{{ old('nomor_surat', $suggestedNomor ?? '') }}" required maxlength="120">
                  <small class="text-muted">Format disarankan: 001/DISHUB/{{ date('Y') }}</small>
               </div>
               <div class="form-group col-md-6">
                  <label>Tanggal Kirim</label>
                  <input type="date" name="tanggal_kirim" class="form-control"
                         value="{{ old('tanggal_kirim', now()->format('Y-m-d')) }}">
               </div>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm mb-2" id="btn-suggest-nomor"
                    data-url="{{ route('persuratan-surat-keluar.suggest-nomor', $persuratan) }}">
               Gunakan Nomor Otomatis
            </button>
            <input type="hidden" name="use_suggested" id="use_suggested" value="0">
            <div class="mt-2">
               <button type="submit" class="btn btn-success">Nomori & Kirim</button>
            </div>
         </form>
      @endif

      @if($s === 'dikirim' && (($flowRoles['sekretariat'] ?? false) || auth()->user()?->is_super_admin))
         <form action="{{ route('persuratan-surat-keluar.archive', $persuratan) }}" method="POST" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-secondary">Arsipkan Surat</button>
         </form>
      @endif

      @can('cancel', $persuratan)
         <form action="{{ route('persuratan-surat-keluar.cancel', $persuratan) }}" method="POST"
               onsubmit="return confirm('Yakin batalkan surat ini?');">
            @csrf
            <div class="form-group">
               <label>Alasan pembatalan <span class="text-danger">*</span></label>
               <textarea name="note" class="form-control" rows="2" required maxlength="2000">{{ old('note') }}</textarea>
            </div>
            <button type="submit" class="btn btn-danger">Batalkan Surat</button>
         </form>
      @endcan
   </div>
</div>

@push('scripts')
<script>
document.getElementById('btn-suggest-nomor')?.addEventListener('click', function () {
   const url = this.dataset.url;
   fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
         document.getElementById('nomor_surat_input').value = data.nomor;
         document.getElementById('use_suggested').value = '1';
      });
});
</script>
@endpush
