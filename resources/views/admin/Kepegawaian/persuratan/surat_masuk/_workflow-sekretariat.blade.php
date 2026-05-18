@php $s = $surat->status; @endphp
<div class="workflow-actions card border mb-4">
   <div class="card-body">
      <h6 class="card-title mb-3">Proses Sekretariat</h6>
      @if($errors->has('workflow'))
         <div class="alert alert-danger">{{ $errors->first('workflow') }}</div>
      @endif
      @can('forwardToKadis', $surat)
         @if(in_array($s, ['tercatat', 'agenda'], true))
            <form action="{{ route('persuratan-masuk.agenda', $surat) }}" method="POST" class="mb-3 border-bottom pb-3">
               @csrf
               <p class="text-muted small mb-2">Berikan nomor agenda tanpa meneruskan ke Kadis.</p>
               <div class="form-group">
                  <label>Catatan (opsional)</label>
                  <textarea name="note" class="form-control" rows="2" maxlength="2000">{{ old('note') }}</textarea>
               </div>
               <button type="submit" class="btn btn-info">Set Agenda & Nomor</button>
            </form>
            <form action="{{ route('persuratan-masuk.forward-kadis', $surat) }}" method="POST" class="mb-3 border-bottom pb-3">
               @csrf
               <p class="text-muted small mb-2">Teruskan ke Kadis untuk disposisi ke Kabid/Unit.</p>
               <div class="form-group">
                  <label>Catatan (opsional)</label>
                  <textarea name="note" class="form-control" rows="2" maxlength="2000">{{ old('note') }}</textarea>
               </div>
               <button type="submit" class="btn btn-success">Teruskan ke Kadis</button>
            </form>
         @endif
      @endcan
      @can('archive', $surat)
         <form action="{{ route('persuratan-masuk.archive', $surat) }}" method="POST" class="mb-3">
            @csrf
            <div class="form-group">
               <label>Catatan arsip (opsional)</label>
               <textarea name="note" class="form-control" rows="2" maxlength="2000">{{ old('note') }}</textarea>
            </div>
            <button type="submit" class="btn btn-dark">Arsipkan</button>
         </form>
      @endcan
      @can('cancel', $surat)
         @if(!in_array($s, ['diarsipkan', 'dibatalkan'], true))
            <form action="{{ route('persuratan-masuk.cancel', $surat) }}" method="POST" class="mb-0" onsubmit="return confirm('Batalkan surat masuk ini?');">
               @csrf
               <div class="form-group">
                  <label>Alasan pembatalan <span class="text-danger">*</span></label>
                  <textarea name="alasan" class="form-control" rows="3" required maxlength="2000">{{ old('alasan') }}</textarea>
               </div>
               <button type="submit" class="btn btn-danger">Batalkan Surat</button>
            </form>
         @endif
      @endcan
   </div>
</div>
