@extends('layouts.app')

@section('title', 'Portal SUNAT | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .sunat-portal-actions {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 0.55rem;
      flex-wrap: wrap;
    }

    .sunat-portal-launcher {
      background: linear-gradient(135deg, #1a3a6b 0%, #24539a 100%);
      color: #fff;
      border: 0;
      border-radius: 12px;
      padding: 0.62rem 0.95rem;
      min-height: 42px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.45rem;
      font-size: 0.84rem;
      font-weight: 700;
      line-height: 1;
      white-space: nowrap;
      cursor: pointer;
      box-shadow: 0 10px 24px rgba(26, 58, 107, 0.22);
      transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
    }

    .sunat-portal-launcher:hover {
      transform: translateY(-1px);
      box-shadow: 0 14px 28px rgba(26, 58, 107, 0.26);
      filter: brightness(1.03);
    }

    .sunat-portal-launcher i {
      font-size: 1rem;
      line-height: 1;
    }

    .sunat-portal-launcher span {
      display: inline-block;
      line-height: 1;
    }

    .sunat-portal-edit {
      flex: 0 0 auto;
    }

    .sunat-config-grid {
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));
      gap:1rem;
      margin-bottom:1.25rem;
    }

    .sunat-config-card {
      background:#f8fafc;
      border:1px solid #e2e8f0;
      border-radius:14px;
      padding:1rem 1.1rem;
    }

    @media (max-width: 900px) {
      .sunat-portal-actions {
        justify-content: flex-end;
      }

      .sunat-portal-launcher {
        width: 100%;
      }
    }
  </style>
@endpush

@section('content')
  <div class="app-layout">
    <aside class="sidebar-premium">
      <div class="sidebar-header">
        <img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta" class="header-logo">
        <div class="header-text">
          <h2>Portal Mendieta</h2>
          <p>Panel interno</p>
        </div>
      </div>
      <hr class="sidebar-divider">
      <div class="sidebar-menu-wrapper">
        <span class="menu-label">MENÚ PRINCIPAL</span>
        @include('partials.sidebar-menu')
      </div>
    </aside>

    <section class="main-wrapper">
      @include('partials.header', [
        'welcomeName' => auth()->user()?->name,
        'userName' => auth()->user()?->name,
        'userEmail' => auth()->user()?->email,
      ])

      <main class="main-content">
        <div class="module-content-stack">

          @foreach(['status' => null, 'success' => null, 'error' => 'module-alert--error'] as $flashKey => $flashClass)
            @if(session($flashKey))
              <div class="placeholder-content module-alert module-flash {{ $flashClass }}" data-flash-message>
                <p>{{ session($flashKey) }}</p>
                <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close><i class='bx bx-x'></i></button>
              </div>
            @endif
          @endforeach

          <div class="placeholder-content module-card-wide">
            <div class="module-toolbar">
              <div>
                <h1>Portal SUNAT</h1>
                <p style="margin:.35rem 0 0; color:#6b7280; font-size:.92rem;">
                  Acceso directo al portal SOL usando las credenciales SUNAT de la empresa activa.
                </p>
              </div>
              <a href="{{ route('companies.index') }}" class="btn-secondary" style="font-size:.85rem;">
                <i class='bx bx-buildings'></i> Ver empresas
              </a>
            </div>

            <div class="sunat-config-grid">
              <div class="sunat-config-card">
                <p style="margin:0; color:#64748b; font-size:.8rem; text-transform:uppercase; letter-spacing:.06em;">Empresa activa</p>
                <p style="margin:.35rem 0 0; color:#0f172a; font-size:1.15rem; font-weight:800;">{{ $company->name }}</p>
                <p style="margin:.35rem 0 0; color:#64748b; font-size:.88rem;">RUC {{ $company->ruc }}</p>
              </div>
              <div class="sunat-config-card">
                <p style="margin:0; color:#64748b; font-size:.8rem; text-transform:uppercase; letter-spacing:.06em;">Estado SUNAT</p>
                <p style="margin:.35rem 0 0; color:#0f172a; font-size:1rem; font-weight:700;">{{ $company->canUseSunatPortal() ? 'Disponible para usar' : 'Pendiente de habilitación' }}</p>
                <p style="margin:.35rem 0 0; color:#64748b; font-size:.88rem;">{{ $company->hasSunatCredentials() ? 'Credenciales SOL configuradas.' : 'Aún faltan credenciales SOL.' }}</p>
              </div>
            </div>

            <div class="module-card-wide" style="background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:1.2rem;">
              <div class="module-toolbar" style="margin-bottom:1rem;">
                <div>
                  <h2 style="margin:0; font-size:1.05rem;">Credenciales SOL de la empresa</h2>
                  <p style="margin:.3rem 0 0; color:#64748b; font-size:.88rem;">Configura aquí el acceso al portal SUNAT para la empresa activa. Ya no depende del catálogo de clientes.</p>
                </div>
                @if($company->canUseSunatPortal() && $company->hasSunatCredentials())
                  <div class="sunat-portal-actions">
                    <button
                      type="button"
                      class="sunat-portal-launcher"
                      title="Ingresar al portal SUNAT SOL"
                      data-sunat-url="{{ route('facturador.sunat.open') }}"
                      data-sunat-nombre="{{ $company->name }}">
                      <i class='bx bx-shield-quarter'></i>
                      <span>Abrir SUNAT</span>
                    </button>
                  </div>
                @endif
              </div>

              @if(! $company->isApproved())
                <div class="placeholder-content module-alert module-alert--error" style="margin-bottom:1rem;">
                  <p>La empresa aún no está aprobada. Podrás usar Portal SUNAT cuando pase a estado aprobada y el administrador lo habilite.</p>
                </div>
              @elseif(! $company->sunat_enabled)
                <div class="placeholder-content module-alert module-alert--error" style="margin-bottom:1rem;">
                  <p>El Portal SUNAT todavía no está habilitado para esta empresa. Puedes dejar las credenciales listas mientras tanto.</p>
                </div>
              @endif

              <form method="POST" action="{{ route('facturador.sunat.credentials.update') }}" class="module-form companies-form-grid">
                @csrf
                @method('PUT')

                <div class="form-group">
                  <label>Usuario SOL</label>
                  <input
                    type="text"
                    name="usuario_sol"
                    class="form-input"
                    autocomplete="off"
                    placeholder="Ej. USUARIO_SOL"
                    value="{{ old('usuario_sol', $company->usuario_sol ?? '') }}">
                  @error('usuario_sol')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                  <label>Clave SOL</label>
                  <input
                    type="password"
                    name="clave_sol"
                    class="form-input"
                    autocomplete="new-password"
                    placeholder="{{ $company->clave_sol ? '(guardada — dejar en blanco para no cambiar)' : 'Ingresar clave SOL' }}">
                  @error('clave_sol')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group full-width profile-actions module-actions">
                  <a href="{{ route('companies.index') }}" class="btn-secondary companies-btn-link">Volver a empresas</a>
                  <button type="submit" class="btn-primary">
                    <i class='bx bx-save'></i> Guardar credenciales
                  </button>
                </div>
              </form>
            </div>

            @include('partials.sunat-modal')
          </div>

          @if($company->canUseFacturador())
            <div class="placeholder-content module-card-wide">
              <div class="module-toolbar">
                <div>
                  <h2>Facturador disponible</h2>
                  <p style="margin:.35rem 0 0; color:#6b7280; font-size:.92rem;">La empresa activa ya puede usar el facturador. El acceso a SUNAT quedó separado para esta misma empresa.</p>
                </div>
                <a href="{{ route('facturador.index') }}" class="btn-secondary">
                  <i class='bx bx-receipt'></i> Ir al facturador
                </a>
              </div>
            </div>
          @endif
          </div>

        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
  <script>
    document.querySelectorAll('[data-flash-message]').forEach((flash) => {
      const closeBtn = flash.querySelector('[data-flash-close]');
      if (closeBtn) {
        closeBtn.addEventListener('click', () => flash.remove());
      }
      window.setTimeout(() => {
        if (document.body.contains(flash)) {
          flash.remove();
        }
      }, 4000);
    });
  </script>
@endpush