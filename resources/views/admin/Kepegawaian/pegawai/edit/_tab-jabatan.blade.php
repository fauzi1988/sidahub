@php
   $isEditing = $editJabatan !== null;
   $formTitle = $isEditing ? 'Ubah Jabatan' : 'Tambah Jabatan';
@endphp
<div class="edit-tab-panel">
   <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
      <div>
         <h5 class="edit-tab-panel__title mb-1">Data Jabatan</h5>
         <p class="text-muted edit-tab-panel__desc mb-0">Kelola riwayat jabatan pegawai pada unit kerja.</p>
      </div>
      @if($isEditing)
         <a href="{{ route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => 'jabatan']) }}" class="btn btn-sm btn-outline-primary">+ Tambah Jabatan Baru</a>
      @endif
   </div>

   <div class="card border mb-4">
      <div class="card-body">
         <h6 class="card-title mb-3">{{ $formTitle }}</h6>

         @if($isEditing)
            <form action="{{ route('jabatan-pegawai.update', $editJabatan) }}" method="POST">
               @csrf
               @method('PUT')
               <input type="hidden" name="id_pegawai" value="{{ $pegawai->id_pegawai }}">
               @include('admin.Kepegawaian.pegawai.edit._jabatan-fields', ['jabatan' => $editJabatan])
               <div class="edit-tab-panel__footer btn-actions mt-3">
                  <button type="submit" class="btn btn-primary">Simpan Perubahan Jabatan</button>
               </div>
            </form>
         @else
            <form action="{{ route('jabatan-pegawai.store') }}" method="POST">
               @csrf
               <input type="hidden" name="id_pegawai" value="{{ $pegawai->id_pegawai }}">
               @include('admin.Kepegawaian.pegawai.edit._jabatan-fields', ['jabatan' => null])
               <input type="hidden" name="tmt" value="{{ old('tmt', now()->toDateString()) }}">
               <input type="hidden" name="status_jabatan" value="{{ old('status_jabatan', 'Aktif') }}">
               <div class="edit-tab-panel__footer btn-actions mt-3">
                  <button type="submit" class="btn btn-primary">Simpan Jabatan</button>
               </div>
            </form>
         @endif
      </div>
   </div>

   <h6 class="mb-3">Riwayat Jabatan</h6>
   @if($pegawai->jabatanPegawai->isEmpty())
      <p class="text-muted mb-0">Belum ada data jabatan. Gunakan formulir di atas untuk menambahkan.</p>
   @else
      <div class="table-responsive">
         <table class="table table-bordered table-hover mb-0">
            <thead class="thead-light">
               <tr>
                  <th>Instansi</th>
                  <th>Jabatan</th>
                  <th>Unit Kerja</th>
                  <th>Pangkat/Gol</th>
                  <th>TMT</th>
                  <th>Status</th>
                  <th width="90">Aksi</th>
               </tr>
            </thead>
            <tbody>
               @foreach($pegawai->jabatanPegawai as $j)
               <tr class="{{ $editJabatan && $editJabatan->id_jabatan === $j->id_jabatan ? 'table-warning' : '' }}">
                  <td>{{ $j->instansi ?? 'Dinas Perhubungan' }}</td>
                  <td>{{ $j->jabatan }}</td>
                  <td>{{ $j->unit_kerja }}</td>
                  <td>{{ $j->pangkat_golongan ?: '-' }}</td>
                  <td>{{ $j->tmt?->format('d/m/Y') }}</td>
                  <td>{{ $j->status_jabatan }}</td>
                  <td>
                     <a href="{{ route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => 'jabatan', 'jabatan' => $j->id_jabatan]) }}"
                        class="btn btn-sm btn-warning">Edit</a>
                  </td>
               </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   @endif
</div>
