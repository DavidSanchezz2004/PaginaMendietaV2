@extends('layouts.app')

@section('title', 'Asignar Cliente a Compra')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .client-option {
      display: block;
      width: 100%;
      box-sizing: border-box;
      padding: 1rem 1.25rem;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
      margin-bottom: 0.75rem;
    }

    .client-option:hover {
      background: rgba(26, 107, 87, 0.05);
      border-color: var(--clr-active-bg, #1a6b57);
    }

    .client-option.selected {
      background: rgba(26, 107, 87, 0.1);
      border-color: var(--clr-active-bg, #1a6b57);
      box-shadow: 0 0 0 3px rgba(26, 107, 87, 0.1);
    }

    .client-name {
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .client-meta {
      font-size: 0.85rem;
      color: var(--clr-text-muted, #6b7280);
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .info-box {
      background: #fff;
      border: 1px solid var(--clr-border-light, #e2e8f0);
      border-radius: 10px;
      padding: 1.25rem;
    }

    .info-label {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--clr-text-muted, #6b7280);
      text-transform: uppercase;
      margin-bottom: 0.5rem;
    }

    .info-value {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--clr-text-main, #374151);
    }

    .search-container {
      position: relative;
      margin-bottom: 1.5rem;
    }

    .search-input {
      width: 100%;
      padding: 0.8rem 1rem;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 0.95rem;
      outline: none;
      font-family: inherit;
    }

    .search-input:focus {
      border-color: var(--clr-active-bg, #1a6b57);
      box-shadow: 0 0 0 3px rgba(26, 107, 87, 0.1);
    }

    .clients-list {
      max-height: 400px;
      overflow-y: auto;
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 1rem;
    }

    .no-results {
      text-align: center;
      padding: 2rem;
      color: var(--clr-text-muted, #6b7280);
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
                <i class='bx bx-user-plus'></i> Asignar Cliente a Compra
              </h1>
              <small style="color:var(--clr-text-muted,#6b7280);">Selecciona el cliente que recibirá la compra</small>
            </div>
            <a href="{{ route('facturador.compras.show', $purchase) }}" class="btn-secondary" style="padding:.6rem 1rem; text-decoration:none;">
              <i class='bx bx-arrow-back'></i> Volver
            </a>
          </div>

          {{-- Alertas --}}
          @if(session('error'))
            <div style="background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.3); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.25rem; color:#991b1b; font-size:.9rem;">
              <i class='bx bx-x-circle' style="margin-right:.4rem;"></i>
              {{ session('error') }}
            </div>
          @endif

          {{-- Información de la compra --}}
          <div class="info-grid">
            <div class="info-box">
              <div class="info-label">Número Compra</div>
              <div class="info-value">{{ $purchase->serie_numero }}</div>
            </div>

            <div class="info-box">
              <div class="info-label">Proveedor</div>
              <div class="info-value">{{ $purchase->provider->nombre_razon_social }}</div>
            </div>

            <div class="info-box">
              <div class="info-label">Total</div>
              <div class="info-value">{{ $purchase->codigo_moneda }} {{ number_format($purchase->monto_total, 2) }}</div>
            </div>

            <div class="info-box">
              <div class="info-label">Fecha</div>
              <div class="info-value">{{ $purchase->fecha_emision->format('d/m/Y') }}</div>
            </div>
          </div>

          {{-- Formulario de selección de cliente --}}
          <form method="POST" action="{{ route('facturador.purchase-client.assign', $purchase) }}" id="formAssignClient">
            @csrf

            <div style="background:#fff; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; padding:1.5rem; box-shadow:0 4px 15px rgba(0,0,0,.03);">
              <h3 style="margin-top:0; margin-bottom:1.5rem; display:flex; align-items:center; gap:.5rem;">
                <i class='bx bx-search'></i> Selecciona un Cliente
              </h3>

              {{-- Búsqueda --}}
              <div class="search-container">
                <input type="text" id="clientSearch" class="search-input" placeholder="Buscar por nombre, RUC/DNI o email...">
              </div>

              {{-- Lista de clientes --}}
              <div class="clients-list" id="clientsList">
                @forelse($clients as $client)
                  <label class="client-option" data-client-id="{{ $client->id }}" data-client-search="{{ strtolower($client->nombre_razon_social . ' ' . $client->numero_documento . ' ' . ($client->correo ?? '')) }}">
                    <input type="radio" name="client_id" value="{{ $client->id }}" style="margin-right:.75rem;" onchange="updateSelectedClient()">
                    <div style="display:inline-block;">
                      <div class="client-name">{{ $client->nombre_razon_social }}</div>
                      <div class="client-meta">{{ $client->numero_documento }}</div>
                      @if($client->correo)
                        <div class="client-meta">{{ $client->correo }}</div>
                      @endif
                      @if($client->is_retainer_agent)
                        <div style="margin-top:.5rem;">
                          <span style="display:inline-block; background:rgba(59,130,246,.1); color:#3b82f6; padding:.2rem .6rem; border-radius:4px; font-size:.75rem; font-weight:600;">
                            <i class='bx bx-shield'></i> Agente Retenedor
                          </span>
                        </div>
                      @endif
                    </div>
                  </label>
                @empty
                  <div class="no-results">
                    <i class='bx bx-inbox' style="font-size:2.5rem; margin-bottom:.5rem;"></i>
                    <p>No hay clientes registrados</p>
                  </div>
                @endforelse
              </div>

              @if($clients->count())
                <div style="display:flex; gap:1rem; margin-top:1.5rem;">
                  <button type="submit" class="btn-primary" style="padding:.6rem 2rem;" id="submitBtn" disabled>
                    <i class='bx bx-check'></i> Asignar Cliente
                  </button>
                  <a href="{{ route('facturador.compras.show', $purchase) }}" class="btn-secondary" style="padding:.6rem 1.5rem; text-decoration:none;">
                    Cancelar
                  </a>
                </div>
              @endif
            </div>
          </form>

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}
@endsection

@push('scripts')
<script>
  const clientSearch = document.getElementById('clientSearch');
  const clientsList = document.getElementById('clientsList');
  const clientOptions = document.querySelectorAll('.client-option');
  const submitBtn = document.getElementById('submitBtn');

  // Búsqueda de clientes
  clientSearch.addEventListener('input', function() {
    const searchText = this.value.toLowerCase();
    const options = clientsList.querySelectorAll('.client-option');
    let visibleCount = 0;

    options.forEach(option => {
      const searchData = option.dataset.clientSearch;
      if (searchData.includes(searchText) || !searchText) {
        option.style.display = '';
        visibleCount++;
      } else {
        option.style.display = 'none';
      }
    });

    if (visibleCount === 0 && searchText) {
      if (!document.getElementById('noSearchResults')) {
        const noResults = document.createElement('div');
        noResults.id = 'noSearchResults';
        noResults.className = 'no-results';
        noResults.innerHTML = '<i class="bx bx-search" style="font-size:2.5rem; margin-bottom:.5rem;"></i><p>No se encontraron clientes que coincidan</p>';
        clientsList.appendChild(noResults);
      }
    } else {
      document.getElementById('noSearchResults')?.remove();
    }
  });

  // Seleccionar cliente
  clientOptions.forEach(option => {
    option.addEventListener('click', function() {
      clientOptions.forEach(o => o.classList.remove('selected'));
      this.classList.add('selected');
      this.querySelector('input[type="radio"]').checked = true;
      updateSelectedClient();
    });
  });

  function updateSelectedClient() {
    const selectedRadio = document.querySelector('input[name="client_id"]:checked');
    submitBtn.disabled = !selectedRadio;
  }

  // Validar antes de enviar
  document.getElementById('formAssignClient').addEventListener('submit', function(e) {
    const selectedRadio = document.querySelector('input[name="client_id"]:checked');
    if (!selectedRadio) {
      e.preventDefault();
      alert('Por favor, selecciona un cliente');
    }
  });
</script>
@endpush
