@php
   $isEditing = $editDokumen !== null;
@endphp
<div class="edit-tab-panel">
   <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
      <div>
         <h5 class="edit-tab-panel__title mb-1">Upload File</h5>
         <p class="text-muted edit-tab-panel__desc mb-0">Unggah dan kelola dokumen pendukung pegawai (maks. 5 MB per file).</p>
      </div>
      @if($isEditing)
         <a href="{{ route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => 'dokumen']) }}" class="btn btn-sm btn-outline-primary">+ Upload Baru</a>
      @endif
   </div>

   <div class="card border mb-4">
      <div class="card-body">
         <h6 class="card-title mb-3">{{ $isEditing ? 'Ubah Dokumen' : 'Upload Dokumen Baru' }}</h6>

         @if($isEditing)
            <form action="{{ route('dokumen-pegawai.update', $editDokumen) }}" method="POST" enctype="multipart/form-data">
               @csrf
               @method('PUT')
               <div class="form-group">
                  <label>Nama Dokumen <span class="text-danger">*</span></label>
                  <input type="text" name="nama_dokumen" class="form-control" maxlength="150"
                         value="{{ old('nama_dokumen', $editDokumen->nama_dokumen) }}" required>
               </div>
               <div class="form-group">
                  <label>Ganti File</label>
                  <input type="file" name="file_dokumen" class="form-control">
                  <small class="text-muted">Kosongkan jika tidak ingin mengganti file.</small>
                  @if($editDokumen->file_dokumen)
                     <div class="mt-2">
                        <a href="{{ asset('storage/'.$editDokumen->file_dokumen) }}" target="_blank" rel="noopener">Lihat file saat ini</a>
                     </div>
                  @endif
               </div>
               <div class="edit-tab-panel__footer btn-actions mt-3">
                  <button type="submit" class="btn btn-primary">Simpan Perubahan Dokumen</button>
               </div>
            </form>
         @else
            <form action="{{ route('dokumen-pegawai.store') }}" method="POST" enctype="multipart/form-data">
               @csrf
               <input type="hidden" name="id_pegawai" value="{{ $pegawai->id_pegawai }}">
               <input type="hidden" name="from_pegawai_edit" value="1">
               <div class="form-group">
                  <label>Nama Dokumen <span class="text-danger">*</span></label>
                  <input type="text" name="nama_dokumen" class="form-control" maxlength="150" value="{{ old('nama_dokumen') }}" required>
               </div>
               <div class="form-group">
                  <label>File <span class="text-danger">*</span></label>
                  <input type="file" name="file_dokumen" class="form-control" required>
                  <small class="text-muted">Maksimal 5 MB.</small>
               </div>
               <div class="edit-tab-panel__footer btn-actions mt-3">
                  <button type="submit" class="btn btn-primary">Upload & Simpan</button>
               </div>
            </form>
         @endif
      </div>
   </div>

   <h6 class="mb-3">Dokumen Tersimpan</h6>
   @if($pegawai->dokumenPegawai->isEmpty())
      <p class="text-muted mb-0">Belum ada dokumen. Gunakan formulir di atas untuk mengunggah.</p>
   @else
      <div class="table-responsive">
         <table class="table table-bordered table-hover mb-0">
            <thead class="thead-light">
               <tr>
                  <th>Nama Dokumen</th>
                  <th>File</th>
                  <th width="120">Aksi</th>
               </tr>
            </thead>
            <tbody>
               @foreach($pegawai->dokumenPegawai as $row)
               <tr class="{{ $editDokumen && $editDokumen->id_dokumen === $row->id_dokumen ? 'table-warning' : '' }}">
                  <td>{{ $row->nama_dokumen }}</td>
                  <td>
                     <a href="{{ asset('storage/'.$row->file_dokumen) }}" target="_blank" rel="noopener">Lihat Dokumen</a>
                  </td>
                  <td>
                     <div class="btn-actions btn-actions--compact">
                        <a href="{{ route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => 'dokumen', 'dokumen' => $row->id_dokumen]) }}"
                           class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('dokumen-pegawai.destroy', $row) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Yakin hapus dokumen ini?');">
                           @csrf
                           @method('DELETE')
                           <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                     </div>
                  </td>
               </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   @endif
</div>
