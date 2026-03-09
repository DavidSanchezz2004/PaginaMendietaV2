@php
  $menu = config('menu.items', []);

  // Helpers
  $isActive = function(array $patterns) {
    foreach ($patterns as $p) {
      if (request()->routeIs($p)) return true;
    }
    return false;
  };
@endphp

<ul class="nav-list">
  @foreach($menu as $item)
    @continue(empty($item['enabled']))

    {{-- Protección de Seguridad vía Policy --}}
    @php
      $canAccessItem = true;
      if (isset($item['can']) && isset($item['model'])) {
          $canAccessItem = auth()->user()->can($item['can'], $item['model']);
      }
    @endphp

    @if($canAccessItem)
      @php
        $hasChildren = !empty($item['children']);
        $open = $hasChildren && $isActive($item['active'] ?? []);
      @endphp

      <li class="nav-item {{ $hasChildren ? 'has-submenu' : '' }} {{ $open ? 'open' : '' }}">
        @if($hasChildren)
          <a href="#" class="nav-link toggle-submenu">
            <div class="nav-link-left">
              <i class='{{ $item['icon'] ?? 'bx bx-circle' }}'></i>
              <span class="nav-text">{{ $item['label'] }}</span>
            </div>
            <i class='bx {{ $open ? "bx-chevron-up" : "bx-chevron-down" }} chevron'></i>
          </a>

          <ul class="submenu">
            @foreach($item['children'] as $child)
              @continue(empty($child['enabled']))

              {{-- Protección de Submenú vía Policy --}}
              @php
                $canAccessChild = true;
                if (isset($child['can']) && isset($child['model'])) {
                    $canAccessChild = auth()->user()->can($child['can'], $child['model']);
                }
              @endphp

              @if($canAccessChild)
                @php
                  $childActive = $isActive($child['active'] ?? []);
                  $childRoute = $child['route'] ?? null;
                  $href = ($childRoute && \Illuminate\Support\Facades\Route::has($childRoute)) ? route($childRoute) : '#';
                @endphp

                <li>
                  <a href="{{ $href }}" class="submenu-link {{ $childActive ? 'active' : '' }}">
                    <i class='{{ $child['icon'] ?? 'bx bx-dot' }}'></i>
                    {{ $child['label'] }}
                  </a>
                </li>
              @endif
            @endforeach
          </ul>
        @else
          @php
            $routeName = $item['route'] ?? null;
            $href = ($routeName && \Illuminate\Support\Facades\Route::has($routeName)) ? route($routeName) : '#';
            $active = $isActive($item['active'] ?? []);
          @endphp

          <a href="{{ $href }}" class="nav-link {{ $active ? 'active' : '' }}">
            <div class="nav-link-left">
              <i class='{{ $item['icon'] ?? 'bx bx-circle' }}'></i>
              <span class="nav-text">{{ $item['label'] }}</span>
            </div>
          </a>
        @endif
      </li>
    @endif
  @endforeach
</ul>
