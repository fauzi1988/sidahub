@php
   $s = $surat->status;
   $myActiveDisposisi = $myActiveDisposisi ?? collect();
@endphp
<div class="workflow-actions card border mb-4">
   <div class="card-body">
      <h6 class="card-title mb-3">Tindak Lanjut Unit</h6>
      <p class="text-muted small">Selesaikan <strong>disposisi Anda</strong> secara terpisah. Surat baru selesai setelah semua unit menyelesaikan disposisinya.</p>
      @if($errors->has('workflow'))
         <div class="alert alert-danger">{{ $errors->first('workflow') }}</div>
      @endif

      @if($myActiveDisposisi->isEmpty() && in_array($s, ['disposisi_ke_unit', 'proses'], true))
         <div class="alert alert-warning mb-0">Tidak ada disposisi aktif untuk akun Anda pada surat ini.</div>
      @endif

      @can('process', $surat)
         @if($s === 'disposisi_ke_unit' && $myActiveDisposisi->isNotEmpty())
            <form action="{{ route('persuratan-masuk.process', $surat) }}" method="POST" class="mb-3 border-bottom pb-3">
               @csrf
               <div class="form-group">
                  <label>Catatan mulai proses (opsional)</label>
                  <textarea name="note" class="form-control" rows="2" maxlength="2000">{{ old('note') }}</textarea>
               </div>
               <button type="submit" class="btn btn-primary btn-sm">Mulai Proses (semua disposisi aktif saya)</button>
            </form>
         @endif
      @endcan

      @foreach($myActiveDisposisi as $disp)
         <div class="border rounded p-3 mb-3 {{ $disp->isOverdue() ? 'border-danger' : '' }}">
            <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
               <div>
                  <strong>Disposisi #{{ $disp->id }}</strong>
                  @if($disp->isOverdue())
                     <span class="badge badge-danger ml-1">Terlambat</span>
                  @endif
               </div>
               <span class="badge badge-primary">{{ $disp->statusLabel() }}</span>
            </div>
            <p class="mb-1 small"><strong>Instruksi:</strong> {{ $disp->instruksi }}</p>
            <p class="mb-2 small text-muted">
               Batas waktu: {{ optional($disp->batas_waktu)->format('d-m-Y') ?: '-' }}
            </p>
            @can('completeDisposisi', [$surat, $disp])
               <form action="{{ route('persuratan-masuk.complete-disposisi', [$surat, $disp]) }}" method="POST" class="mb-0">
                  @csrf
                  <div class="form-group mb-2">
                     <label>Catatan penyelesaian (opsional)</label>
                     <textarea name="note" class="form-control" rows="2" maxlength="2000">{{ old('note') }}</textarea>
                  </div>
                  <button type="submit" class="btn btn-success btn-sm">Tandai Disposisi Ini Selesai</button>
               </form>
            @endcan
         </div>
      @endforeach

      @if($surat->status === 'selesai')
         <div class="alert alert-success mb-0">Semua disposisi telah selesai. Menunggu arsip dari Sekretariat.</div>
      @endif
   </div>
</div>
