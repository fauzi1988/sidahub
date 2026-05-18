<div class="workflow-actions card border mb-4">
   <div class="card-body">
      <h6 class="card-title mb-3">Disposisi Kadis</h6>
      @if($errors->has('workflow'))
         <div class="alert alert-danger">{{ $errors->first('workflow') }}</div>
      @endif
      @if($errors->has('disposisi'))
         <div class="alert alert-danger">{{ $errors->first('disposisi') }}</div>
      @endif
      @can('kadisDispose', $surat)
         <form action="{{ route('persuratan-masuk.kadis-dispose', $surat) }}" method="POST" class="mb-0" id="form-disposisi-kadis">
            @csrf
            <p class="text-muted small mb-2">
               Disposisi pertama wajib diisi. Baris tambahan bersifat opsional — jika dikosongkan atau dihapus, tidak akan disimpan.
            </p>
            <div id="disposisi-rows">
               <div class="disposisi-row disposisi-row-primary border rounded p-3 mb-2">
                  <div class="form-row">
                     <div class="form-group col-md-4">
                        <label>Pegawai (opsional)</label>
                        <select name="disposisi[0][to_pegawai_id]" class="form-control">
                           <option value="">-- Pilih Pegawai --</option>
                           @foreach($pegawaiOptions as $p)
                              <option value="{{ $p->id_pegawai }}" @selected(old('disposisi.0.to_pegawai_id') == $p->id_pegawai)>{{ $p->nama_lengkap }} @if($p->nip)({{ $p->nip }})@endif</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="form-group col-md-4">
                        <label>Unit Kerja (opsional)</label>
                        <select name="disposisi[0][to_unit_kerja]" class="form-control">
                           <option value="">-- Pilih Unit --</option>
                           @foreach($unitOptions as $unit)
                              <option value="{{ $unit }}" @selected(old('disposisi.0.to_unit_kerja') === $unit)>{{ $unit }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="form-group col-md-4">
                        <label>Batas Waktu</label>
                        <input type="date" name="disposisi[0][batas_waktu]" class="form-control" value="{{ old('disposisi.0.batas_waktu') }}">
                     </div>
                  </div>
                  <div class="form-group mb-0">
                     <label>Instruksi <span class="text-danger">*</span></label>
                     <textarea name="disposisi[0][instruksi]" class="form-control disposisi-instruksi-wajib" rows="2" required maxlength="2000">{{ old('disposisi.0.instruksi') }}</textarea>
                  </div>
               </div>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm mb-2" id="btn-add-disposisi">+ Tambah Disposisi (opsional)</button>
            <div class="form-group">
               <label>Catatan Kadis (opsional)</label>
               <textarea name="note" class="form-control" rows="2" maxlength="2000">{{ old('note') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Disposisi</button>
         </form>
         <template id="tpl-disposisi-row">
            <div class="disposisi-row disposisi-row-extra border rounded p-3 mb-2">
               <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-muted small">Disposisi tambahan (opsional)</span>
                  <button type="button" class="btn btn-outline-danger btn-sm btn-remove-disposisi" title="Hapus baris">Hapus</button>
               </div>
               <div class="form-row">
                  <div class="form-group col-md-4">
                     <label>Pegawai (opsional)</label>
                     <select name="disposisi[__IDX__][to_pegawai_id]" class="form-control">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($pegawaiOptions as $p)
                           <option value="{{ $p->id_pegawai }}">{{ $p->nama_lengkap }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="form-group col-md-4">
                     <label>Unit Kerja (opsional)</label>
                     <select name="disposisi[__IDX__][to_unit_kerja]" class="form-control">
                        <option value="">-- Pilih Unit --</option>
                        @foreach($unitOptions as $unit)
                           <option value="{{ $unit }}">{{ $unit }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="form-group col-md-4">
                     <label>Batas Waktu</label>
                     <input type="date" name="disposisi[__IDX__][batas_waktu]" class="form-control">
                  </div>
               </div>
               <div class="form-group mb-0">
                  <label>Instruksi</label>
                  <textarea name="disposisi[__IDX__][instruksi]" class="form-control disposisi-instruksi-opsional" rows="2" maxlength="2000" placeholder="Kosongkan jika tidak dipakai"></textarea>
               </div>
            </div>
         </template>
         <script>
            (function () {
               var idx = 1;
               var btn = document.getElementById('btn-add-disposisi');
               var container = document.getElementById('disposisi-rows');
               var tpl = document.getElementById('tpl-disposisi-row');
               var form = document.getElementById('form-disposisi-kadis');
               if (!btn || !container || !tpl) return;

               function bindRemove(row) {
                  var removeBtn = row.querySelector('.btn-remove-disposisi');
                  if (!removeBtn) return;
                  removeBtn.addEventListener('click', function () {
                     row.remove();
                  });
               }

               btn.addEventListener('click', function () {
                  var html = tpl.innerHTML.replace(/__IDX__/g, String(idx++));
                  var wrap = document.createElement('div');
                  wrap.innerHTML = html.trim();
                  var row = wrap.firstElementChild;
                  if (!row) return;
                  container.appendChild(row);
                  bindRemove(row);
               });

               if (form) {
                  form.addEventListener('submit', function () {
                     container.querySelectorAll('.disposisi-row-extra').forEach(function (row) {
                        var ta = row.querySelector('textarea[name*="[instruksi]"]');
                        if (ta && !ta.value.trim()) {
                           row.querySelectorAll('input, select, textarea').forEach(function (el) {
                              el.removeAttribute('name');
                           });
                        }
                     });
                  });
               }
            })();
         </script>
      @endcan
   </div>
</div>
