@php
   $isEditing = $editPendidikan !== null;
@endphp
<div class="edit-tab-panel">
   <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
      <div>
         <h5 class="edit-tab-panel__title mb-1">Data Pendidikan</h5>
         <p class="text-muted edit-tab-panel__desc mb-0">Kelola riwayat pendidikan formal pegawai.</p>
      </div>
      @if($isEditing)
         <a href="{{ route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => 'pendidikan']) }}" class="btn btn-sm btn-outline-primary">+ Tambah Pendidikan</a>
      @endif
   </div>

   <div class="card border mb-4">
      <div class="card-body">
         <h6 class="card-title mb-3">{{ $isEditing ? 'Ubah Pendidikan' : 'Tambah Pendidikan' }}</h6>

         @if($isEditing)
            <form action="{{ route('pendidikan.update', $editPendidikan) }}" method="POST">
               @csrf
               @method('PUT')
               <input type="hidden" name="id_pegawai" value="{{ $pegawai->id_pegawai }}">
               @include('admin.Kepegawaian.pegawai.edit._pendidikan-fields', ['pendidikan' => $editPendidikan])
               <div class="edit-tab-panel__footer btn-actions mt-3">
                  <button type="submit" class="btn btn-primary">Simpan Perubahan Pendidikan</button>
               </div>
            </form>
         @else
            <form action="{{ route('pendidikan.store') }}" method="POST">
               @csrf
               <input type="hidden" name="id_pegawai" value="{{ $pegawai->id_pegawai }}">
               <input type="hidden" name="from_pegawai_edit" value="1">
               @include('admin.Kepegawaian.pegawai.edit._pendidikan-fields', ['pendidikan' => null])
               <div class="edit-tab-panel__footer btn-actions mt-3">
                  <button type="submit" class="btn btn-primary">Simpan Pendidikan</button>
               </div>
            </form>
         @endif
      </div>
   </div>

   <h6 class="mb-3">Riwayat Pendidikan</h6>
   @if($pegawai->pendidikanPegawai->isEmpty())
      <p class="text-muted mb-0">Belum ada data pendidikan. Gunakan formulir di atas untuk menambahkan.</p>
   @else
      <div class="table-responsive">
         <table class="table table-bordered table-hover mb-0">
            <thead class="thead-light">
               <tr>
                  <th>Tingkat</th>
                  <th>Jurusan</th>
                  <th>Institusi</th>
                  <th>Tahun Lulus</th>
                  <th width="120">Aksi</th>
               </tr>
            </thead>
            <tbody>
               @foreach($pegawai->pendidikanPegawai as $pd)
               <tr class="{{ $editPendidikan && $editPendidikan->id_pendidikan === $pd->id_pendidikan ? 'table-warning' : '' }}">
                  <td>{{ $pd->tingkat }}</td>
                  <td>{{ $pd->jurusan }}</td>
                  <td>{{ $pd->nama_institusi }}</td>
                  <td>{{ $pd->tahun_lulus }}</td>
                  <td>
                     <div class="btn-actions btn-actions--compact">
                        <a href="{{ route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => 'pendidikan', 'pendidikan' => $pd->id_pendidikan]) }}"
                           class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('pendidikan.destroy', $pd) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Yakin hapus data pendidikan ini?');">
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
