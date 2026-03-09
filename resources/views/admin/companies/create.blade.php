@extends('layouts.app')

@section('title', 'Registrar Empresa | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
  @php
    $isEditing = isset($company);
    $formAction = $isEditing ? route('companies.update', $company) : route('companies.store');
    $rucValue = old('ruc', $company->ruc ?? '');
    $nameValue = old('name', $company->name ?? '');
    $statusValue = old('status', $company->status ?? 'active');
    $facturadorEnabled = (bool) old('facturador_enabled', $company->facturador_enabled ?? false);
  @endphp

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
        @if (session('status'))
          <div class="placeholder-content module-alert module-flash" data-flash-message>
            <p>{{ session('status') }}</p>
            <button type="button" class="module-flash-close" aria-label="Cerrar mensaje" data-flash-close>
              <i class='bx bx-x'></i>
            </button>
          </div>
        @endif

        @if ($errors->any())
          <div class="placeholder-content module-alert">
            @foreach ($errors->all() as $error)
              <p>{{ $error }}</p>
            @endforeach
          </div>
        @endif

        <div class="placeholder-content module-card-wide companies-module-card">
          <div class="module-toolbar companies-form-header">
            <h1>{{ $isEditing ? 'Editar Empresa' : 'Registrar Empresa' }}</h1>
          </div>
          <p class="companies-form-subtitle">
            {{ $isEditing ? 'Actualiza la información general de la empresa.' : 'Completa los datos para crear una nueva empresa en el portal.' }}
          </p>

          <form id="company-form" class="module-form companies-form-grid" method="POST" action="{{ $formAction }}">
            @csrf
            @if($isEditing)
              @method('PATCH')
            @endif

            <div class="form-group">
              <label>RUC</label>
              <div class="input-with-action">
                <input type="text" id="ruc" name="ruc" class="form-input" value="{{ $rucValue }}" minlength="11" maxlength="11" required>
                <button type="button" id="btn-lookup-ruc" class="btn-secondary" @disabled($isEditing)>Consultar RUC</button>
              </div>
            </div>

            <div class="form-group">
              <label>Razón Social</label>
              <input type="text" id="name" name="name" class="form-input" value="{{ $nameValue }}" required>
            </div>

            <div class="form-group full-width">
              <label>Dirección (SUNAT)</label>
              <input type="text" id="direccion" class="form-input" value="{{ old('direccion') }}" readonly>
            </div>

            <div class="form-group full-width">
              <label>Ubicación (Departamento - Provincia - Distrito)</label>
              <input type="text" id="ubicacion" class="form-input" value="{{ old('ubicacion') }}" readonly>
            </div>

            <div class="form-group">
              <label>Estado</label>
              <select name="status" id="status" class="form-input" required>
                <option value="active" @selected($statusValue === 'active')>Activo</option>
                <option value="inactive" @selected($statusValue === 'inactive')>Inactivo</option>
              </select>
            </div>

            <div class="form-group full-width profile-actions module-actions">
              <a href="{{ route('companies.index') }}" class="btn-secondary companies-btn-link">Cancelar</a>
              <button type="submit" class="btn-primary">
                <i class='bx bx-save'></i> {{ $isEditing ? 'Actualizar Empresa' : 'Guardar Empresa' }}
              </button>
            </div>
          </form>

          @if($isEditing && auth()->user()?->can('updateSunatCredentials', $company))
          <div class="placeholder-content module-card-wide" style="margin-top:1.5rem;">
            <div class="module-toolbar" style="margin-bottom:1rem;">
              <div>
                <h2 style="font-size:1.1rem;"><i class='bx bx-shield-quarter' style="margin-right:.4rem;"></i>Credenciales SOL — Portal SUNAT</h2>
                <p style="margin:.3rem 0 0; color:#64748b; font-size:.88rem;">
                  Configura el Usuario y Clave SOL para que esta empresa pueda ingresar al Portal SUNAT.
                </p>
              </div>
              @if($company->hasSunatCredentials())
                <span style="display:inline-flex; align-items:center; gap:.35rem; background:#dcfce7; color:#166534; border-radius:999px; padding:.3rem .8rem; font-size:.78rem; font-weight:700;">
                  <i class='bx bx-check-circle'></i> Credenciales configuradas
                </span>
              @else
                <span style="display:inline-flex; align-items:center; gap:.35rem; background:#fef3c7; color:#92400e; border-radius:999px; padding:.3rem .8rem; font-size:.78rem; font-weight:700;">
                  <i class='bx bx-error-circle'></i> Pendiente de configurar
                </span>
              @endif
            </div>

            <form method="POST" action="{{ route('portal-sunat.credentials.update', $company) }}" class="module-form companies-form-grid">
              @csrf
              @method('PUT')
              <input type="hidden" name="redirect_back" value="companies.edit">

              <div class="form-group">
                <label>Usuario SOL</label>
                <input type="text" name="usuario_sol" class="form-input" autocomplete="off"
                  placeholder="Ej. MIUSUARIOSOL"
                  value="{{ old('usuario_sol', $company->usuario_sol ?? '') }}">
              </div>

              <div class="form-group">
                <label>Clave SOL</label>
                <input type="password" name="clave_sol" class="form-input" autocomplete="new-password"
                  placeholder="{{ $company->clave_sol ? '(guardada — dejar en blanco para no cambiar)' : 'Ingresar clave SOL' }}">
              </div>

              <div class="form-group full-width profile-actions module-actions">
                <button type="submit" class="btn-primary">
                  <i class='bx bx-save'></i> Guardar credenciales SOL
                </button>
              </div>
            </form>
          </div>
          @endif

          @if($isEditing)
          <div class="placeholder-content module-card-wide" style="margin-top:1.5rem;">
            <div class="module-toolbar">
              <h2 style="font-size:1.1rem;"><i class='bx bx-group' style="margin-right:.4rem;"></i>Usuarios asignados</h2>
              <span style="font-size:.85rem; color:#64748b;">Gestiona quién tiene acceso a esta empresa.</span>
            </div>

            @if(isset($assignedUsers) && $assignedUsers->count())
              <div class="module-table-wrap" style="margin-bottom:1.25rem;">
                <table class="module-table">
                  <thead>
                    <tr>
                      <th>Nombre</th>
                      <th>Email</th>
                      <th>Rol en empresa</th>
                      <th>Estado</th>
                      <th class="cell-action">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($assignedUsers as $au)
                      @php
                        $pivotRole   = $au->pivot?->role instanceof \App\Enums\RoleEnum ? $au->pivot->role->value : (string) $au->pivot?->role;
                        $pivotStatus = $au->pivot?->status ?? 'active';
                        $canRemove   = auth()->user()?->role?->value === 'admin' || $pivotRole === 'auxiliar';
                      @endphp
                      <tr>
                        <td style="font-weight:600;">{{ $au->name }}</td>
                        <td style="color:#475569; font-size:.88rem;">{{ $au->email }}</td>
                        <td>
                          <span class="companies-status-pill {{ in_array($pivotRole, ['admin','supervisor']) ? 'is-active' : '' }}" style="text-transform:capitalize;">
                            {{ $pivotRole }}
                          </span>
                        </td>
                        <td>
                          <span class="companies-status-pill {{ $pivotStatus === 'active' ? 'is-active' : 'is-inactive' }}">
                            {{ $pivotStatus === 'active' ? 'Activo' : 'Inactivo' }}
                          </span>
                        </td>
                        <td class="cell-action">
                          @if($canRemove)
                            <form method="POST" action="{{ route('companies.users.remove', [$company, $au]) }}" data-confirm-remove>
                              @csrf
                              <button type="submit" class="btn-action-icon" title="Quitar de empresa">
                                <i class='bx bx-user-minus'></i>
                              </button>
                            </form>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <p style="color:#94a3b8; font-size:.9rem; margin-bottom:1.25rem;">No hay usuarios asignados todavía.</p>
            @endif

            {{-- Asignar nuevo usuario --}}
            @if(isset($assignableUsers) && $assignableUsers->count())
              <form method="POST" action="{{ route('companies.users.assign', $company) }}" class="module-form" style="display:flex; gap:.75rem; align-items:flex-end; flex-wrap:wrap;">
                @csrf
                <div class="form-group" style="margin:0; flex:1 1 200px;">
                  <label style="font-size:.82rem;">Usuario</label>
                  <select name="user_id" class="form-input" required>
                    <option value="">Seleccionar usuario…</option>
                    @foreach($assignableUsers as $u)
                      <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group" style="margin:0; flex:0 1 160px;">
                  <label style="font-size:.82rem;">Rol en empresa</label>
                  <select name="role" class="form-input" required>
                    <option value="auxiliar">Auxiliar</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="client">Cliente</option>
                    @if(auth()->user()?->role?->value === 'admin')
                      <option value="admin">Admin</option>
                    @endif
                  </select>
                </div>
                <div style="padding-bottom:.1rem;">
                  <button type="submit" class="btn-primary">
                    <i class='bx bx-user-plus'></i> Asignar
                  </button>
                </div>
              </form>
            @endif
          </div>

          <div class="placeholder-content module-card-wide" style="margin-top:1.5rem;">
            <div class="module-toolbar">
              <h2 style="font-size:1.1rem;"><i class='bx bx-receipt' style="margin-right:.4rem;"></i>Facturador</h2>
            </div>
            <p style="font-size:.88rem; color:#6b7280; margin-bottom:1.25rem;">
              Habilita o deshabilita el módulo de facturación electrónica para esta empresa.<br>
              <strong>El Token Feasy es único y global</strong> (una cuenta Feasy gestiona todas las empresas por RUC).
              Confíguralo en
              <a href="{{ route('configuracion.feasy.edit') }}" style="color:#1a6b57;">Facturador › Configuración Feasy</a>.
            </p>

            <form method="POST" action="{{ route('configuracion.companies.facturador.update', $company) }}" class="module-form">
              @csrf
              @method('PUT')

              <div class="companies-form-grid">
                <div class="form-group">
                  <label>Estado del Facturador</label>
                  <label class="company-toggle-label" style="margin-top:.4rem; display:flex; align-items:center; gap:.5rem;">
                    <input type="hidden" name="facturador_enabled" value="0">
                    <input type="checkbox" name="facturador_enabled" value="1" @checked($company->facturador_enabled)>
                    <span>Habilitar Facturador para esta empresa</span>
                  </label>
                </div>

                <div class="form-group">
                  <label>Token Feasy (global)</label>
                  @php $globalToken = config('services.feasy.token', ''); @endphp
                  @if($globalToken)
                    <p style="font-size:.85rem; color:#1a6b57;"><i class='bx bx-check-circle'></i> Token configurado ({{ strlen($globalToken) }} caracteres)</p>
                  @else
                    <p style="font-size:.85rem; color:#dc2626;"><i class='bx bx-error-circle'></i> Sin token —
                      <a href="{{ route('configuracion.feasy.edit') }}" style="color:#dc2626;">Configurar ahora</a>
                    </p>
                  @endif
                </div>
              </div>

              <div class="form-group full-width profile-actions module-actions" style="margin-top:1rem;">
                <button type="submit" class="btn-primary">
                  <i class='bx bx-save'></i> Guardar
                </button>
              </div>
            </form>
          </div>
          @endif
        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
  <script>

    const btnLookup = document.getElementById('btn-lookup-ruc');
    const rucInput = document.getElementById('ruc');
    const nameInput = document.getElementById('name');
    const direccionInput = document.getElementById('direccion');
    const ubicacionInput = document.getElementById('ubicacion');
    const statusInput = document.getElementById('status');

    if (btnLookup && rucInput && !btnLookup.disabled) {
      btnLookup.addEventListener('click', async () => {
        const ruc = (rucInput.value || '').trim();

        if (!/^\d{11}$/.test(ruc)) {
          alert('El RUC debe tener 11 dígitos numéricos.');
          return;
        }

        btnLookup.disabled = true;
        const originalText = btnLookup.textContent;
        btnLookup.textContent = 'Consultando...';

        try {
          const response = await fetch('{{ route('companies.lookup-ruc') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ruc })
          });

          const payload = await response.json();

          if (!response.ok || !payload.success) {
            const errorText = payload?.message || payload?.errors?.ruc?.[0] || 'No se pudo consultar el RUC.';
            alert(errorText);
            return;
          }

          nameInput.value = payload.data.name || '';
          direccionInput.value = payload.data.direccion || '';
          ubicacionInput.value = payload.data.ubicacion || '';

          if (payload.data.status) {
            statusInput.value = payload.data.status;
          }
        } catch (error) {
          alert('Error de conexión consultando el RUC.');
        } finally {
          btnLookup.disabled = false;
          btnLookup.textContent = originalText;
        }
      });
    }

    const hideFlashMessage = (flash) => {
      flash.classList.add('is-hiding');
      window.setTimeout(() => flash.remove(), 220);
    };

    document.querySelectorAll('[data-confirm-remove]').forEach((form) => {
      form.addEventListener('submit', (e) => {
        if (! confirm('¿Quitar a este usuario de la empresa?')) e.preventDefault();
      });
    });

    document.querySelectorAll('[data-flash-message]').forEach((flash) => {
      const closeBtn = flash.querySelector('[data-flash-close]');
      if (closeBtn) {
        closeBtn.addEventListener('click', () => hideFlashMessage(flash));
      }

      window.setTimeout(() => {
        if (document.body.contains(flash)) {
          hideFlashMessage(flash);
        }
      }, 4000);
    });
  </script>
@endpush
