<header class="top-header">
  @php
    $authUser = auth()->user();
    $resolvedAvatarUrl = $avatarUrl
      ?? ($authUser?->profile?->avatar_path ? asset('storage/'.$authUser->profile->avatar_path) : 'https://images.pexels.com/photos/2379004/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=150');
    $authRole = $authUser?->role instanceof \App\Enums\RoleEnum
      ? $authUser->role->value
      : (string) ($authUser?->role ?? '');
    $activeCompanyId = (int) session('company_id');
    $availableCompanies = $authUser
      ? $authUser->companies()
          ->wherePivot('status', 'active')
          ->where('companies.status', 'active')
          ->select('companies.id', 'companies.name', 'companies.ruc')
          ->orderBy('companies.name')
          ->get()
      : collect();
    $activeCompany = $availableCompanies->firstWhere('id', $activeCompanyId) ?? $availableCompanies->first();
    $shouldShowCompanySelector = $availableCompanies->isNotEmpty() && (
      in_array($authRole, [\App\Enums\RoleEnum::CLIENT->value, \App\Enums\RoleEnum::AUXILIAR->value], true)
      || $availableCompanies->count() > 1
    );
  @endphp

  <div class="header-left">
    <h2 class="welcome-text" style="white-space: nowrap;">
      ¡Bienvenido {{ $welcomeName ?? 'Usuario' }}!
    </h2>

    @if($shouldShowCompanySelector)
      <div class="header-divider"></div>

      <div class="company-selector-container" id="company-selector-container" style="position: relative; z-index: 100;">
        <div class="company-selector-btn" id="company-btn" role="button" tabindex="0">
          <div class="company-info">
            <i class='bx bx-buildings company-icon'></i>
            <div class="company-text">
              <span class="company-label">Empresa Activa</span>
              <strong class="company-name" id="active-company-name">{{ $activeCompany?->name ?? 'Sin empresa activa' }}</strong>
            </div>
          </div>
          <i class='bx bx-chevron-down' id="company-chevron"></i>
        </div>

        <div class="company-dropdown" id="company-dropdown" style="background: var(--clr-bg-card, #fff); border-color: var(--clr-border-light, #e5e7eb);">
          <div class="dropdown-search" style="border-bottom-color: var(--clr-border-light, #f3f4f6);">
            <i class='bx bx-search' style="color: var(--clr-text-muted, #9ca3af);"></i>
            <input type="text" id="company-search-input" placeholder="Buscar empresa..." style="background: transparent; color: var(--clr-text-main, #111827);">
          </div>
          <ul class="company-list" id="company-list">
            @foreach($availableCompanies as $company)
              @php
                $isActive = $activeCompany && $activeCompany->id === $company->id;
                $initials = collect(explode(' ', trim($company->name)))
                  ->filter()
                  ->map(fn (string $part) => mb_substr($part, 0, 1))
                  ->take(2)
                  ->implode('');
              @endphp
              <li>
                <form method="POST" action="{{ route('active-company.update') }}">
                  @csrf
                  @method('PATCH')
                  <input type="hidden" name="company_id" value="{{ $company->id }}">
                  <button type="submit" class="company-item {{ $isActive ? 'active' : '' }}" style="width:100%; border:none; background:transparent; text-align:left; border-bottom: 1px solid var(--clr-border-light, #f3f4f6);">
                    <div class="company-avatar">{{ $initials }}</div>
                    <div class="company-details">
                      <strong style="color: var(--clr-text-main, #111827);">{{ $company->name }}</strong>
                      <span style="color: var(--clr-text-muted, #6b7280);">RUC: {{ $company->ruc }}</span>
                    </div>
                    <i class='bx bx-check check-icon' style="color: #3b82f6;"></i>
                  </button>
                </form>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif
  </div>

  <div class="header-right">

    <!-- Botón Modo Claro/Oscuro -->
    <button class="icon-btn" id="theme-toggle" title="Cambiar Tema">
      <i class='bx bx-moon' id="theme-icon"></i>
    </button>

    <!-- Perfil de Usuario con Dropdown -->
    <div class="user-profile-container" id="profile-container">
      <div class="user-profile" id="profile-btn">
        <img
          src="{{ $resolvedAvatarUrl }}"
          alt="Usuario"
          class="avatar-img"
        >
        <i class='bx bx-chevron-down' id="profile-chevron"></i>
      </div>

      <div class="profile-dropdown" id="profile-dropdown">
        <div class="dropdown-header">
          <strong>{{ $userName ?? 'Julio Mendoza' }}</strong>
          <span>{{ $userEmail ?? 'julio@mendieta.pe' }}</span>
        </div>

        <hr class="dropdown-divider">

        <ul class="dropdown-menu">
          <li>
            <a href="{{ route('profile') }}">
              <i class='bx bx-user'></i> Mi Perfil
            </a>
          </li>

          <hr class="dropdown-divider">

          <li>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-action text-danger">
                <i class='bx bx-log-out'></i> Cerrar Sesión
              </button>
            </form>
          </li>
        </ul>
      </div>
    </div>

  </div>
</header>

<script>
  (() => {
    const companyBtn = document.getElementById('company-btn');
    const companyDropdown = document.getElementById('company-dropdown');
    const companyChevron = document.getElementById('company-chevron');
    const companySearchInput = document.getElementById('company-search-input');
    const companyList = document.getElementById('company-list');

    if (!companyBtn || !companyDropdown || !companyChevron) {
      return;
    }

    const closeCompanyDropdown = () => {
      companyDropdown.classList.remove('show');
      companyChevron.style.transform = 'rotate(0deg)';
    };

    const openCompanyDropdown = () => {
      companyDropdown.classList.add('show');
      companyChevron.style.transform = 'rotate(180deg)';
      if (companySearchInput) {
        companySearchInput.focus();
      }
    };

    companyBtn.addEventListener('click', (event) => {
      event.stopPropagation();
      if (companyDropdown.classList.contains('show')) {
        closeCompanyDropdown();
      } else {
        openCompanyDropdown();
      }
    });

    companyBtn.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        companyBtn.click();
      }
    });

    document.addEventListener('click', (event) => {
      const container = document.getElementById('company-selector-container');
      if (container && !container.contains(event.target)) {
        closeCompanyDropdown();
      }
    });

    if (companySearchInput && companyList) {
      companySearchInput.addEventListener('input', () => {
        const term = companySearchInput.value.toLowerCase().trim();
        companyList.querySelectorAll('li').forEach((item) => {
          const text = item.textContent?.toLowerCase() ?? '';
          item.style.display = text.includes(term) ? '' : 'none';
        });
      });
    }
  })();
</script>