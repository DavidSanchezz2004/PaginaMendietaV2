@php
  $menu = config('menu.items', []);
  $userRole = auth()->user()?->role?->value ?? (string) auth()->user()?->role;

  // Helpers
  $isActive = function(array $patterns) {
    foreach ($patterns as $p) {
      if (request()->routeIs($p)) return true;
    }
    return false;
  };

  // Validar si rol está permitido para item
  $isRoleAllowed = function($item) use ($userRole) {
    // Si está en la blacklist de roles excluidos
    if (isset($item['exclude_roles']) && in_array($userRole, (array)$item['exclude_roles'])) {
      return false;
    }
    // Si existe una whitelist de roles permitidos
    if (isset($item['only_roles']) && !in_array($userRole, (array)$item['only_roles'])) {
      return false;
    }
    return true;
  };
@endphp

<ul class="nav-list">
  @foreach($menu as $item)
    @continue(empty($item['enabled']))
    @continue(!$isRoleAllowed($item))

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
              @continue(!$isRoleAllowed($child))

              {{-- Protección de Submenú vía Policy --}}
              @php
                $canAccessChild = true;
                if (isset($child['can']) && isset($child['model'])) {
                    $canAccessChild = auth()->user()->can($child['can'], $child['model']);
                }
              @endphp

              @if($canAccessChild)
                @if(($child['type'] ?? '') === 'section')
                  <li><span class="submenu-section-label">{{ $child['label'] }}</span></li>
                @else
                  @php
                    $childActive = $isActive($child['active'] ?? []);
                    $childUrl   = $child['url'] ?? null;
                    $childRoute = $child['route'] ?? null;
                    $href   = $childUrl ?? (($childRoute && \Illuminate\Support\Facades\Route::has($childRoute)) ? route($childRoute) : '#');
                    $target = $child['target'] ?? '_self';
                    $rel    = $target === '_blank' ? 'noopener noreferrer' : '';
                  @endphp
                  <li>
                    <a href="{{ $href }}" target="{{ $target }}"@if($rel) rel="{{ $rel }}"@endif class="submenu-link {{ $childActive ? 'active' : '' }}">
                      <i class='{{ $child['icon'] ?? 'bx bx-dot' }}'></i>
                      {{ $child['label'] }}
                    </a>
                  </li>
                @endif
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
