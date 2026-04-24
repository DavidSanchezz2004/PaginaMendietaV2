@extends('layouts.app')

@section('title', 'Direcciones de ' . $client->nombre_razon_social)

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .address-card {
      background: #fff;
      border: 1px solid var(--clr-border-light, #e2e8f0);
      border-radius: 10px;
      padding: 1.25rem;
      position: relative;
      transition: all 0.2s;
    }

    .address-card:hover {
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      border-color: var(--clr-active-bg, #1a6b57);
    }

    .address-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
    }

    .badge-default {
      background: rgba(34, 197, 94, 0.1);
      color: #22c55e;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }

    .badge-type {
      background: rgba(59, 130, 246, 0.1);
      color: #3b82f6;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }

    .address-actions {
      display: flex;
      gap: 0.5rem;
      margin-top: 1rem;
    }

    .modal-backdrop {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 999;
    }

    .modal-backdrop.active {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background: white;
      border-radius: 14px;
      padding: 2rem;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-label {
      font-size: 0.85rem;
      font-weight: 600;
      display: block;
      margin-bottom: 0.4rem;
      color: var(--clr-text-main, #374151);
    }

    .form-input,
    .form-select {
      width: 100%;
      padding: 0.6rem 0.85rem;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 0.9rem;
      outline: none;
      font-family: inherit;
      box-sizing: border-box;
    }

    .form-input:focus,
    .form-select:focus {
      border-color: var(--clr-active-bg, #1a6b57);
      box-shadow: 0 0 0 3px rgba(26, 107, 87, 0.1);
    }
  </style>
@endpush

@section('content')
<div class="app-layout">
  <aside class="sidebar-premium">
    <div class="sidebar-header">
      <img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta" class="header-logo">
      <div class="header-text"><h2>Portal Mendieta</h2><p>Panel interno</p></div>
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
      'userName'    => auth()->user()?->name,
      'userEmail'   => auth()->user()?->email,
    ])

    <main class="main-content">
      <div class="module-content-stack">
        <div class="placeholder-content module-card-wide">

          {{-- Header --}}
          <div class="module-toolbar">
            <div>
              <h1 style="display:flex; align-items:center; gap:.5rem; margin-bottom:.15rem;">
                <i class='bx bx-map-pin'></i> Direcciones
              </h1>
              <small style="color:var(--clr-text-muted,#6b7280);">{{ $client->nombre_razon_social }}</small>
            </div>
            <div style="display:flex; gap:.5rem;">
              <button id="btnAddAddress" class="btn-primary" style="padding:.6rem 1.25rem;">
                <i class='bx bx-plus'></i> Nueva Dirección
              </button>
              <a href="{{ route('facturador.clients.index') }}" class="btn-secondary" style="padding:.6rem 1rem; text-decoration:none;">
                <i class='bx bx-arrow-back'></i> Volver
              </a>
            </div>
          </div>

          {{-- Alertas --}}
          @if(session('success'))
            <div style="background:rgba(34,197,94,.08); border:1px solid rgba(34,197,94,.3); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.25rem; color:#166534; font-size:.9rem;">
              <i class='bx bx-check-circle' style="margin-right:.4rem;"></i>
              {{ session('success') }}
            </div>
          @endif

          @if($errors->any())
            <div style="background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.3); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.25rem; color:#991b1b; font-size:.9rem;">
              <i class='bx bx-x-circle' style="margin-right:.4rem;"></i>
              <strong>Errores encontrados:</strong>
              <ul style="margin:.5rem 0 0 0; padding-left:1.5rem;">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Direcciones --}}
          @if($client->addresses->count())
            <div class="address-grid">
              @foreach($client->addresses as $address)
                <div class="address-card">
                  <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:1rem;">
                    <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                      @if($address->is_default)
                        <span class="badge-default">
                          <i class='bx bx-star' style="margin-right:.25rem;"></i> Predeterminada
                        </span>
                      @endif
                      <span class="badge-type">{{ $address->type_name }}</span>
                    </div>
                  </div>

                  <div style="margin-bottom:1rem;">
                    <div style="font-weight:600; margin-bottom:.25rem;">{{ $address->street }}</div>
                    <div style="font-size:.9rem; color:var(--clr-text-muted,#6b7280);">
                      {{ $address->city }}@if($address->state), {{ $address->state }}@endif
                    </div>
                    @if($address->postal_code)
                      <div style="font-size:.85rem; color:var(--clr-text-muted,#6b7280);">
                        CP: {{ $address->postal_code }}
                      </div>
                    @endif
                  </div>

                  <div class="address-actions">
                    <button class="btn-secondary edit-address" data-address="{{ json_encode($address) }}" style="padding:.4rem .7rem; font-size:.85rem; flex:1;">
                      <i class='bx bx-pencil'></i> Editar
                    </button>

                    @if(!$address->is_default)
                      <form method="POST" action="{{ route('facturador.client-addresses.set-default', [$client, $address]) }}" style="flex:1; display:flex;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-secondary" style="padding:.4rem .7rem; font-size:.85rem; width:100%;">
                          <i class='bx bx-star'></i> Por defecto
                        </button>
                      </form>
                    @endif

                    @if($client->addresses->count() > 1)
                      <form method="POST" action="{{ route('facturador.client-addresses.destroy', [$client, $address]) }}" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta dirección?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary" style="padding:.4rem .7rem; font-size:.85rem; color:#dc2626;">
                          <i class='bx bx-trash'></i>
                        </button>
                      </form>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:2rem; text-align:center;">
              <i class='bx bx-map' style="font-size:3rem; color:#cbd5e1; margin-bottom:.5rem;"></i>
              <p style="color:#6b7280; margin:0; margin-bottom:1rem;">No hay direcciones registradas</p>
              <button id="btnAddAddressEmpty" class="btn-primary" style="padding:.6rem 1.25rem;">
                <i class='bx bx-plus'></i> Agregar Primera Dirección
              </button>
            </div>
          @endif

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}

{{-- Modal para crear/editar dirección --}}
<div class="modal-backdrop" id="addressModal">
  <div class="modal-content">
    <h2 id="modalTitle" style="margin-top:0;">Nueva Dirección</h2>

    <form method="POST" id="addressForm">
      @csrf
      <input type="hidden" name="_method" id="formMethod" value="POST">

      <div class="form-group">
        <label class="form-label">Tipo de Dirección</label>
        <select name="type" class="form-select" required>
          <option value="">-- Seleccionar --</option>
          <option value="fiscal">Fiscal</option>
          <option value="delivery">Entrega</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Calle</label>
        <input type="text" name="street" class="form-input" placeholder="Calle, número, apartamento" required>
      </div>

      <div class="form-group">
        <label class="form-label">Ciudad</label>
        <input type="text" name="city" class="form-input" placeholder="Ciudad" required>
      </div>

      <div class="form-group">
        <label class="form-label">Departamento/Estado</label>
        <input type="text" name="state" class="form-input" placeholder="Departamento">
      </div>

      <div class="form-group">
        <label class="form-label">Código Postal</label>
        <input type="text" name="postal_code" class="form-input" placeholder="Código postal">
      </div>

      <div class="form-group" id="defaultCheckbox" style="display:none;">
        <input type="hidden" name="is_default" value="0">
        <label style="display:flex; align-items:center; gap:.5rem; font-weight:500; cursor:pointer;">
          <input type="checkbox" name="is_default" value="1">
          <span>Usar como dirección predeterminada</span>
        </label>
      </div>

      <div style="display:flex; gap:1rem; margin-top:2rem;">
        <button type="submit" class="btn-primary" style="padding:.6rem 1.5rem; flex:1;">
          Guardar Dirección
        </button>
        <button type="button" id="cancelBtn" class="btn-secondary" style="padding:.6rem 1.5rem; flex:1;">
          Cancelar
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const modal = document.getElementById('addressModal');
  const form = document.getElementById('addressForm');
  const formMethod = document.getElementById('formMethod');
  const modalTitle = document.getElementById('modalTitle');
  const defaultCheckbox = document.getElementById('defaultCheckbox');

  // Abrir modal para nueva dirección
  document.getElementById('btnAddAddress')?.addEventListener('click', openNewModal);
  document.getElementById('btnAddAddressEmpty')?.addEventListener('click', openNewModal);

  function openNewModal() {
    formMethod.value = 'POST';
    modalTitle.textContent = 'Nueva Dirección';
    form.action = '{{ route("facturador.client-addresses.store", $client) }}';
    form.reset();
    defaultCheckbox.style.display = 'block';
    modal.classList.add('active');
  }

  // Editar dirección
  document.querySelectorAll('.edit-address').forEach(btn => {
    btn.addEventListener('click', function() {
      const address = JSON.parse(this.dataset.address);
      formMethod.value = 'PATCH';
      modalTitle.textContent = 'Editar Dirección';
      form.action = `{{ route('facturador.client-addresses.update', ['client' => $client, 'address' => ':id']) }}`.replace(':id', address.id);

      form.type.value = address.type;
      form.street.value = address.street;
      form.city.value = address.city;
      form.state.value = address.state || '';
      form.postal_code.value = address.postal_code || '';
      form.querySelector('input[name="is_default"]').checked = address.is_default;

      defaultCheckbox.style.display = 'none';
      modal.classList.add('active');
    });
  });

  // Cerrar modal
  document.getElementById('cancelBtn').addEventListener('click', closeModal);
  modal.addEventListener('click', function(e) {
    if (e.target === modal) closeModal();
  });

  function closeModal() {
    modal.classList.remove('active');
    form.reset();
  }
</script>
@endpush
