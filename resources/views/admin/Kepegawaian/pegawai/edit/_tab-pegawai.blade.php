<div class="edit-tab-panel">
   <h5 class="edit-tab-panel__title">Data Pegawai</h5>
   <p class="text-muted edit-tab-panel__desc">Perbarui identitas dan kontak pegawai, lalu simpan perubahan di tab ini.</p>

   <form action="{{ route('pegawai.update', $pegawai) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      @include('admin.Kepegawaian.pegawai._form', ['pegawai' => $pegawai, 'showActions' => false])

      <div class="edit-tab-panel__footer btn-actions">
         <button type="submit" class="btn btn-primary">Simpan Data Pegawai</button>
      </div>
   </form>
</div>
