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

          @if($isEditing)
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
