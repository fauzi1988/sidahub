@php
    /** @var \App\Models\User|null $u */
    $u = auth()->user();
@endphp
@if($u)
<ul class="list-unstyled components">
   @foreach(config('menu_access.modules') as $module)
      @php
          $items = $module['items'] ?? [];
          $visibleItems = collect($items)->filter(fn ($item) => $u->hasPermission($item['key']));
      @endphp

      @if(count($items) === 0 && $u->hasPermission($module['key']))
         <li>
            <a href="{{ isset($module['route']) && $module['route'] ? route($module['route']) : '#' }}">
               <i class="fa {{ $module['icon'] ?? 'fa-circle-o' }}"></i>
               <span>{{ $module['label'] }}</span>
            </a>
         </li>
      @elseif($visibleItems->isNotEmpty())
         @php $collapseId = 'menu-' . $module['key']; @endphp
         <li>
            <a href="#{{ $collapseId }}" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
               <i class="fa {{ $module['icon'] ?? 'fa-folder-o' }}"></i>
               <span>{{ $module['label'] }}</span>
            </a>
            <ul class="collapse list-unstyled" id="{{ $collapseId }}">
               @foreach($visibleItems as $item)
                  <li>
                     <a href="{{ !empty($item['route']) ? route($item['route']) : '#' }}" @if(empty($item['route'])) class="text-muted" title="Menu akan tersedia" @endif>
                        &gt; <span>{{ $item['label'] }}</span>
                     </a>
                  </li>
               @endforeach
            </ul>
         </li>
      @endif
   @endforeach
</ul>
@endif
