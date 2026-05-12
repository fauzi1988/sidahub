@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Detail Pegawai</h2>
         <div class="btn-actions">
            <a href="{{ route('pegawai.index') }}" class="btn btn-secondary">Kembali</a>
            <a href="{{ route('pegawai.edit', $pegawai) }}" class="btn btn-warning">Edit</a>
            @if(auth()->user()->hasPermission('pengaturan.pengguna') && ! $pegawai->user)
               <a href="{{ route('pengguna.create', ['id_pegawai' => $pegawai->id_pegawai]) }}" class="btn btn-primary">Buat akun aplikasi</a>
            @endif
         </div>
      </div>
   </div>
</div>

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
   </div>
@endif

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <ul class="nav nav-pills detail-pegawai-tabs mb-3" id="detailPegawaiTabs" role="tablist">
         <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'pegawai' ? 'active' : '' }}"
               id="tab-pegawai"
               href="{{ route('pegawai.show', ['pegawai' => $pegawai, 'tab' => 'pegawai']) }}"
               role="tab">Pegawai</a>
         </li>
         <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'jabatan' ? 'active' : '' }}"
               id="tab-jabatan"
               href="{{ route('pegawai.show', ['pegawai' => $pegawai, 'tab' => 'jabatan']) }}"
               role="tab">Jabatan</a>
         </li>
         <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'pendidikan' ? 'active' : '' }}"
               id="tab-pendidikan"
               href="{{ route('pegawai.show', ['pegawai' => $pegawai, 'tab' => 'pendidikan']) }}"
               role="tab">Pendidikan</a>
         </li>
      </ul>

      <div class="tab-content">
         <div class="tab-pane fade {{ $activeTab === 'pegawai' ? 'show active' : '' }}" id="panel-pegawai" role="tabpanel">
            <div class="table-responsive">
               <table class="table table-bordered">
                  <tr><th width="30%">ID Pegawai</th><td>{{ $pegawai->id_pegawai }}</td></tr>
                  <tr>
                     <th>Foto</th>
                     <td>
                        @if($pegawai->foto)
                           <img src="{{ asset('storage/'.$pegawai->foto) }}" alt="Foto Pegawai" style="max-height:140px; border-radius:8px;">
                        @else
                           -
                        @endif
                     </td>
                  </tr>
                  <tr><th>NIP</th><td>{{ $pegawai->nip ?: '-' }}</td></tr>
                  <tr><th>NIK</th><td>{{ $pegawai->nik }}</td></tr>
                  <tr><th>Nama Lengkap</th><td>{{ $pegawai->nama_lengkap }}</td></tr>
                  <tr><th>Gelar Depan</th><td>{{ $pegawai->gelar_depan ?: '-' }}</td></tr>
                  <tr><th>Gelar Belakang</th><td>{{ $pegawai->gelar_belakang ?: '-' }}</td></tr>
                  <tr><th>Tempat Lahir</th><td>{{ $pegawai->tempat_lahir }}</td></tr>
                  <tr><th>Tanggal Lahir</th><td>{{ $pegawai->tanggal_lahir?->format('d-m-Y') }}</td></tr>
                  <tr><th>Jenis Kelamin</th><td>{{ $pegawai->jenis_kelamin }}</td></tr>
                  <tr><th>Agama</th><td>{{ $pegawai->agama }}</td></tr>
                  <tr><th>Status Kepegawaian</th><td>{{ $pegawai->status_kepegawaian }}</td></tr>
                  <tr><th>Alamat KTP</th><td>{{ $pegawai->alamat_ktp }}</td></tr>
                  <tr><th>No HP</th><td>{{ $pegawai->no_hp }}</td></tr>
                  <tr><th>Email Dinas</th><td>{{ $pegawai->email_dinas ?: '-' }}</td></tr>
                  <tr>
                     <th>Akun aplikasi</th>
                     <td>
                        @if($pegawai->user)
                           <span class="d-block text-success mb-1">Terhubung ke akun: <strong>{{ $pegawai->user->email }}</strong> ({{ $pegawai->user->name }})</span>
                           @if(auth()->user()->hasPermission('pengaturan.pengguna'))
                              <a href="{{ route('pengguna.edit', $pegawai->user) }}" class="btn btn-sm btn-outline-primary">Kelola akun</a>
                           @endif
                        @else
                           <span class="text-muted d-block mb-2">Belum ada akun login untuk pegawai ini.</span>
                           @if(auth()->user()->hasPermission('pengaturan.pengguna'))
                              <a href="{{ route('pengguna.create', ['id_pegawai' => $pegawai->id_pegawai]) }}" class="btn btn-sm btn-primary">Buat akun aplikasi</a>
                           @endif
                        @endif
                     </td>
                  </tr>
               </table>
            </div>
         </div>

         <div class="tab-pane fade {{ $activeTab === 'jabatan' ? 'show active' : '' }}" id="panel-jabatan" role="tabpanel">
            <div class="mb-3 btn-actions">
               <a href="{{ route('jabatan-pegawai.create', ['id_pegawai' => $pegawai->id_pegawai]) }}" class="btn btn-primary btn-sm">Tambah Jabatan</a>
            </div>
            @if($pegawai->jabatanPegawai->isEmpty())
               <p class="text-muted mb-0">Belum ada data jabatan untuk pegawai ini.</p>
            @else
               <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                     <thead class="thead-light">
                        <tr>
                           <th>Instansi</th>
                           <th>Jabatan</th>
                           <th>Unit Kerja</th>
                           <th>Pangkat/Golongan</th>
                           <th>TMT</th>
                           <th>Status</th>
                           <th>Aksi</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($pegawai->jabatanPegawai as $j)
                        <tr>
                           <td>{{ $j->instansi ?? 'Dinas Perhubungan' }}</td>
                           <td>{{ $j->jabatan }}</td>
                           <td>{{ $j->unit_kerja }}</td>
                           <td>{{ $j->pangkat_golongan ?: '-' }}</td>
                           <td>{{ $j->tmt?->format('d/m/Y') }}</td>
                           <td>{{ $j->status_jabatan }}</td>
                           <td>
                              <div class="btn-actions btn-actions--compact">
                                 <a href="{{ route('jabatan-pegawai.edit', $j) }}" class="btn btn-sm btn-warning">Edit</a>
                              </div>
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
            @endif
         </div>

         <div class="tab-pane fade {{ $activeTab === 'pendidikan' ? 'show active' : '' }}" id="panel-pendidikan" role="tabpanel">
            <div class="mb-3 btn-actions">
               <a href="{{ route('pendidikan.create', ['id_pegawai' => $pegawai->id_pegawai]) }}" class="btn btn-primary btn-sm">Tambah Pendidikan</a>
            </div>
            @if($pegawai->pendidikanPegawai->isEmpty())
               <p class="text-muted mb-0">Belum ada data pendidikan untuk pegawai ini.</p>
            @else
               <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                     <thead class="thead-light">
                        <tr>
                           <th>Tingkat</th>
                           <th>Jurusan</th>
                           <th>Institusi</th>
                           <th>Tahun Lulus</th>
                           <th>Aksi</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($pegawai->pendidikanPegawai as $pd)
                        <tr>
                           <td>{{ $pd->tingkat }}</td>
                           <td>{{ $pd->jurusan }}</td>
                           <td>{{ $pd->nama_institusi }}</td>
                           <td>{{ $pd->tahun_lulus }}</td>
                           <td>
                              <div class="btn-actions btn-actions--compact">
                                 <a href="{{ route('pendidikan.show', $pd) }}" class="btn btn-sm btn-secondary">Detail</a>
                                 <a href="{{ route('pendidikan.edit', $pd) }}" class="btn btn-sm btn-warning">Edit</a>
                                 <form action="{{ route('pendidikan.destroy', $pd) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?');">
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
      </div>
   </div>
</div>
@endsection
