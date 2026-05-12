@csrf

@if(!isset($user))
   @if(isset($pegawaiSiap) && $pegawaiSiap->isNotEmpty())
      <div class="form-group">
         <label for="id_pegawai">Pegawai <span class="text-danger">*</span></label>
         <select name="id_pegawai" id="id_pegawai" class="form-control @error('id_pegawai') is-invalid @enderror" required>
            <option value="">— Pilih pegawai (harus sudah terdaftar di Data Pegawai) —</option>
            @foreach($pegawaiSiap as $p)
               @php
                  $labelNama = trim(implode(' ', array_filter([$p->gelar_depan, $p->nama_lengkap, $p->gelar_belakang])));
               @endphp
               <option value="{{ $p->id_pegawai }}"
                  @selected((string) old('id_pegawai', $prefillId ?? null) === (string) $p->id_pegawai)>
                  {{ $p->nip ?: 'Tanpa NIP' }} — {{ $labelNama }}
               </option>
            @endforeach
         </select>
         @error('id_pegawai')<div class="invalid-feedback">{{ $message }}</div>@enderror
         <small class="text-muted">Hanya pegawai yang belum punya akun yang tercantum. Tambah data pegawai di menu Kepegawaian jika belum ada.</small>
      </div>
   @endif
@else
   <div class="form-group">
      <label>Pegawai</label>
      <div class="border rounded px-3 py-2 bg-light">
         @if($user->pegawai)
            <strong>{{ $user->pegawai->nip ?: '—' }}</strong>
            —
            @php
               $pn = $user->pegawai;
               $labelNama = trim(implode(' ', array_filter([$pn->gelar_depan, $pn->nama_lengkap, $pn->gelar_belakang])));
            @endphp
            {{ $labelNama }}
            <a href="{{ route('pegawai.show', $user->pegawai) }}" class="btn btn-sm btn-outline-primary ml-2">Lihat data pegawai</a>
         @else
            <span class="text-muted">Belum terhubung ke data pegawai.</span>
         @endif
      </div>
      <small class="text-muted">Pemilihan pegawai tidak dapat diubah dari sini demi keamanan data.</small>
   </div>
@endif

<div class="row">
   <div class="col-md-6">
      <div class="form-group">
         <label for="name">Nama tampilan akun <span class="text-danger">*</span></label>
         <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', isset($user) ? $user->name : '') }}" required maxlength="255">
         @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
   </div>
   <div class="col-md-6">
      <div class="form-group">
         <label for="email">Email login <span class="text-danger">*</span></label>
         <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', isset($user) ? $user->email : '') }}" required maxlength="255">
         @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
   </div>
</div>

<div class="row">
   <div class="col-md-6">
      <div class="form-group">
         <label for="password">
            {{ isset($user) ? 'Kata sandi baru' : 'Kata sandi' }}
            @if(!isset($user))<span class="text-danger">*</span>@endif
         </label>
         <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                {{ isset($user) ? '' : 'required minlength=8' }} autocomplete="new-password">
         @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
         @if(isset($user))
            <small class="text-muted">Kosongkan jika tidak ingin mengubah kata sandi.</small>
         @endif
      </div>
   </div>
   <div class="col-md-6">
      <div class="form-group">
         <label for="password_confirmation">
            {{ isset($user) ? 'Ulangi kata sandi baru' : 'Konfirmasi kata sandi' }}
            @if(!isset($user))<span class="text-danger">*</span>@endif
         </label>
         <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                {{ isset($user) ? '' : 'required' }} autocomplete="new-password">
      </div>
   </div>
</div>

@if(auth()->user()->is_super_admin)
<div class="form-group">
   <label class="d-flex align-items-center mb-0 cursor-pointer">
      <input type="hidden" name="is_super_admin" value="0">
      <input type="checkbox" name="is_super_admin" value="1" class="mr-2"
             @checked(old('is_super_admin', isset($user) && $user->is_super_admin))>
      <span>Super admin (akses penuh semua menu tanpa centang detail)</span>
   </label>
   @error('is_super_admin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>
@endif

@php
    $hideMatrix = isset($user) && $user->is_super_admin;
    if (old('is_super_admin') && auth()->user()->is_super_admin) {
        $hideMatrix = true;
    }
@endphp
<div id="permission-matrix-wrap" class="{{ $hideMatrix ? 'd-none' : '' }}">
   @include('admin.pengguna._permission_matrix')
</div>

@if(auth()->user()->is_super_admin)
<script>
(function () {
  var cb = document.querySelector('input[name="is_super_admin"][type="checkbox"]');
  var wrap = document.getElementById('permission-matrix-wrap');
  if (!cb || !wrap) return;
  function sync() {
    wrap.classList.toggle('d-none', cb.checked);
  }
  cb.addEventListener('change', sync);
  sync();
})();
</script>
@endif

@if(!isset($user) && isset($pegawaiMap) && count($pegawaiMap))
<script>
(function () {
  var map = @json($pegawaiMap);
  var sel = document.getElementById('id_pegawai');
  var nameEl = document.getElementById('name');
  var emailEl = document.getElementById('email');
  if (!sel || !nameEl || !emailEl) return;
  function apply() {
    var key = String(sel.value || '');
    if (!key || !map[key]) return;
    if (!nameEl.value.trim()) {
      nameEl.value = map[key].nama || '';
    }
    if (!emailEl.value.trim() && map[key].email) {
      emailEl.value = map[key].email;
    }
  }
  sel.addEventListener('change', apply);
  document.addEventListener('DOMContentLoaded', apply);
})();
</script>
@endif
