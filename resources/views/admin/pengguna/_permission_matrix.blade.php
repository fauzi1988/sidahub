@php
    $selected = collect(old('permissions', isset($user) ? $user->permissionKeys() : []))->flip();
@endphp
<div class="permission-matrix card border-0 shadow-sm mb-3">
   <div class="card-body p-0">
      <p class="text-muted small px-3 pt-3 mb-2">Centang sub menu yang boleh diakses pengguna ini. Modul tanpa centangan tidak akan tampil di sidebar.</p>
      @foreach(config('menu_access.modules') as $module)
         @php
             $items = $module['items'] ?? [];
         @endphp
         @if(count($items) === 0)
            @continue
         @endif
         <div class="permission-matrix__group border-bottom">
            <div class="d-flex align-items-center px-3 py-2 bg-light">
               <label class="mb-0 font-weight-bold d-flex align-items-center cursor-pointer">
                  <input type="checkbox"
                         class="permission-matrix__parent mr-2"
                         data-group="{{ $module['key'] }}"
                         title="Pilih semua di {{ $module['label'] }}">
                  <span>{{ $module['label'] }}</span>
               </label>
            </div>
            <div class="px-3 py-2 pl-4">
               <div class="row">
                  @foreach($items as $item)
                     <div class="col-md-6 col-lg-4 mb-2">
                        <label class="mb-0 small d-flex align-items-start cursor-pointer">
                           <input type="checkbox"
                                  name="permissions[]"
                                  value="{{ $item['key'] }}"
                                  class="permission-matrix__child mr-2 mt-1"
                                  data-group="{{ $module['key'] }}"
                                  @checked($selected->has($item['key']))>
                           <span>{{ $item['label'] }}</span>
                        </label>
                     </div>
                  @endforeach
               </div>
            </div>
         </div>
      @endforeach
   </div>
</div>
<style>
.permission-matrix .cursor-pointer { cursor: pointer; }
.permission-matrix__group:last-child { border-bottom: none !important; }
</style>
<script>
(function () {
  document.querySelectorAll('.permission-matrix__parent').forEach(function (parent) {
    parent.addEventListener('change', function () {
      var g = this.getAttribute('data-group');
      var on = this.checked;
      document.querySelectorAll('.permission-matrix__child[data-group="' + g + '"]').forEach(function (cb) {
        cb.checked = on;
      });
    });
  });
  document.querySelectorAll('.permission-matrix__child').forEach(function (child) {
    child.addEventListener('change', function () {
      var g = this.getAttribute('data-group');
      var siblings = document.querySelectorAll('.permission-matrix__child[data-group="' + g + '"]');
      var parent = document.querySelector('.permission-matrix__parent[data-group="' + g + '"]');
      if (!parent) return;
      var allOn = true, anyOn = false;
      siblings.forEach(function (cb) {
        if (!cb.checked) allOn = false;
        if (cb.checked) anyOn = true;
      });
      parent.checked = allOn;
      parent.indeterminate = anyOn && !allOn;
    });
  });
  document.querySelectorAll('.permission-matrix__group').forEach(function (group) {
    var parent = group.querySelector('.permission-matrix__parent');
    if (!parent) return;
    var g = parent.getAttribute('data-group');
    var siblings = group.querySelectorAll('.permission-matrix__child[data-group="' + g + '"]');
    var allOn = true, anyOn = false;
    siblings.forEach(function (cb) {
      if (!cb.checked) allOn = false;
      if (cb.checked) anyOn = true;
    });
    parent.checked = allOn && siblings.length;
    parent.indeterminate = anyOn && !allOn;
  });
})();
</script>
