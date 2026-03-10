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

{{-- ── Links de interés (visible para todos los roles) ─────── --}}
@php
  $userRoleVal = auth()->user()?->role instanceof \App\Enums\RoleEnum
    ? auth()->user()->role->value
    : (string) auth()->user()?->role;
@endphp
@if(auth()->check())
<div class="sidebar-section-links">
  <hr class="sidebar-divider" style="margin: .75rem 0;">
  <span class="menu-label">LINKS DE INTERÉS</span>

  <div class="sidebar-links-group">
    <span class="sidebar-links-title">SUNAT</span>
    <a href="https://ww1.sunat.gob.pe/ol-at-ittramitedoc/registro/iniciar" target="_blank" rel="noopener noreferrer" class="sidebar-ext-link">
      <i class='bx bx-send'></i> Mesa de Partes
    </a>
    <a href="https://www.sunat.gob.pe/orientacion/cronogramas/2026/cObligacionMensual2026.html" target="_blank" rel="noopener noreferrer" class="sidebar-ext-link">
      <i class='bx bx-calendar'></i> Cronograma 2026
    </a>
    <a href="https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/FrameCriterioBusquedaWeb.jsp" target="_blank" rel="noopener noreferrer" class="sidebar-ext-link">
      <i class='bx bx-search'></i> Consulta RUC
    </a>
    <a href="https://ww1.sunat.gob.pe/xssecurity/SignOnVerification.htm?signonForwardAction=https%3A%2F%2Fww1.sunat.gob.pe%2Fol-ti-itrheemision%2Femisionrhe.do" target="_blank" rel="noopener noreferrer" class="sidebar-ext-link">
      <i class='bx bx-file'></i> Emitir RH
    </a>
    <a href="https://ww1.sunat.gob.pe/xssecurity/SignOnVerification.htm?signonForwardAction=https%3A%2F%2Fww1.sunat.gob.pe%2Fol-ti-itrheemisionnce%2Femisionnce.do" target="_blank" rel="noopener noreferrer" class="sidebar-ext-link">
      <i class='bx bx-file-blank'></i> Nota crédito RH
    </a>
    <a href="https://e-consulta.sunat.gob.pe/ol-ti-itconsvalicpe/ConsValiCpe.htm" target="_blank" rel="noopener noreferrer" class="sidebar-ext-link">
      <i class='bx bx-check-shield'></i> Validar CPE
    </a>
  </div>

  <div class="sidebar-links-group" style="margin-top:.6rem;">
    <span class="sidebar-links-title">Otros</span>
    <a href="https://aplicativosweb6.sunafil.gob.pe/si.mesaVirtual/registro" target="_blank" rel="noopener noreferrer" class="sidebar-ext-link">
      <i class='bx bx-inbox'></i> Mesa SUNAFIL
    </a>
    <a href="https://tribunalfiscal.pegasus.com.pe/" target="_blank" rel="noopener noreferrer" class="sidebar-ext-link">
      <i class='bx bx-buildings'></i> Tribunal Fiscal
    </a>
  </div>
</div>
@endif
