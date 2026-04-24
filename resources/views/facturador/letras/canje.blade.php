@extends('layouts.app')

@section('title', 'Canjear a Letras — Compra ' . $purchase->serie_numero)

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .cuota-row { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: .75rem; align-items: end; margin-bottom: .75rem; }
    .resumen-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; }
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
        <i class='bx bx-transfer' style="color:var(--clr-text-main);"></i> Canjear a Letras de Cambio
      </h1>
      <small style="color:var(--clr-text-muted,#6b7280);">Compra {{ $purchase->serie_numero }} · {{ $purchase->razon_social_proveedor }}</small>
    </div>
    <a href="{{ route('facturador.compras.show', $purchase) }}" class="btn-secondary" style="font-size:.85rem; padding:.5rem .9rem;">
      <i class='bx bx-arrow-back'></i> Volver
    </a>
  </div>

  {{-- Alerta si ya tiene letras --}}
  @if($purchase->letras->count() > 0)
    <div style="background:rgba(245,158,11,.08); border:1px solid rgba(245,158,11,.3); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.25rem; color:#92400e; font-size:.9rem;">
      <i class='bx bx-info-circle' style="margin-right:.4rem;"></i>
      Esta compra ya tiene <strong>{{ $purchase->letras->count() }} letra(s)</strong> generada(s)
      por un total de {{ $purchase->codigo_moneda }} {{ number_format($purchase->letras->sum('monto'), 2) }}.
      Generar letras nuevamente creará registros adicionales.
    </div>
  @endif

  <div style="display:grid; grid-template-columns:{{ $purchase->letras->count() ? '1fr 300px' : '1fr' }}; gap:1.5rem; align-items:start;">

    {{-- Panel principal --}}
    <div style="background:#fff; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,.03);">
      <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--clr-border-light,rgba(0,0,0,.06)); font-weight:600; font-size:.92rem; display:flex; align-items:center; gap:.5rem;">
        <i class='bx bx-list-ul'></i> Configurar Cuotas
      </div>
      <div style="padding:1.5rem;">

        {{-- Referencia de montos --}}
        <div class="resumen-box" style="margin-bottom:1.5rem;">
          <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; text-align:center;">
            <div>
              <div style="font-size:.8rem; color:var(--clr-text-muted,#6b7280); margin-bottom:.25rem;">Total compra</div>
              <div style="font-weight:700;">{{ $purchase->codigo_moneda }} {{ number_format($purchase->monto_total, 2) }}</div>
            </div>
            <div>
              <div style="font-size:.8rem; color:var(--clr-text-muted,#6b7280); margin-bottom:.25rem;">Detracción / Retención</div>
              <div style="font-weight:700; color:#dc2626;">
                - {{ number_format(($purchase->monto_detraccion ?? 0) + ($purchase->monto_retencion ?? 0), 2) }}
              </div>
            </div>
            <div>
              <div style="font-size:.8rem; color:var(--clr-text-muted,#6b7280); margin-bottom:.25rem;">Monto neto para letras</div>
              <div style="font-weight:800; font-size:1.15rem; color:var(--clr-active-bg,#1a6b57);" id="montoNetoDisplay">
                {{ $purchase->codigo_moneda }}
                {{ number_format($purchase->monto_total - ($purchase->monto_detraccion ?? 0) - ($purchase->monto_retencion ?? 0), 2) }}
              </div>
            </div>
          </div>
        </div>

        <form method="POST" action="{{ route('facturador.compras.canjear', $purchase) }}" id="formCanje">
          @csrf

          {{-- Opciones opcionales --}}
          <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
            <div>
              <label style="font-size:.85rem; font-weight:600; color:var(--clr-text-main,#374151); display:block; margin-bottom:.4rem;">Lugar de Giro</label>
              <input type="text" name="lugar_giro" value="LIMA"
                     style="width:100%; padding:.55rem .85rem; border:1px solid #e2e8f0; border-radius:8px; font-size:.9rem; outline:none; font-family:inherit; box-sizing:border-box;">
            </div>
            <div>
              <label style="font-size:.85rem; font-weight:600; color:var(--clr-text-main,#374151); display:block; margin-bottom:.4rem;">Banco (opcional)</label>
              <input type="text" name="banco" placeholder="Ej: BCP"
                     style="width:100%; padding:.55rem .85rem; border:1px solid #e2e8f0; border-radius:8px; font-size:.9rem; outline:none; font-family:inherit; box-sizing:border-box;">
            </div>
            <div>
              <label style="font-size:.85rem; font-weight:600; color:var(--clr-text-main,#374151); display:block; margin-bottom:.4rem;">Cuenta bancaria (opcional)</label>
              <input type="text" name="banco_cuenta" placeholder="Ej: 193-12345678-0-12"
                     style="width:100%; padding:.55rem .85rem; border:1px solid #e2e8f0; border-radius:8px; font-size:.9rem; outline:none; font-family:inherit; box-sizing:border-box;">
            </div>
          </div>

          <hr style="border:none; border-top:1px solid #e2e8f0; margin:1rem 0;">

          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:.5rem;">
            <span style="font-weight:600; font-size:.92rem;">Cuotas</span>
            <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
              <button type="button" id="btnPresetUna" class="btn-secondary" style="font-size:.8rem; padding:.35rem .75rem;">1 cuota</button>
              <button type="button" id="btnPreset2"   class="btn-secondary" style="font-size:.8rem; padding:.35rem .75rem;">2 cuotas (50/50)</button>
              <button type="button" id="btnPreset3"   class="btn-secondary" style="font-size:.8rem; padding:.35rem .75rem;">3 cuotas iguales</button>
              <button type="button" id="btnAddCuota"  class="btn-primary"   style="font-size:.8rem; padding:.35rem .75rem;">
                <i class='bx bx-plus'></i> Agregar
              </button>
            </div>
          </div>

          {{-- Encabezado columnas --}}
          <div class="cuota-row" style="font-size:.82rem; color:#6b7280; font-weight:600;">
            <span>Días plazo</span>
            <span>Porcentaje %</span>
            <span>Monto estimado</span>
            <span></span>
          </div>

          <div id="cuotasContainer"></div>

          {{-- Totalizador --}}
          <div style="display:flex; justify-content:space-between; align-items:center; margin-top:.75rem; padding:.75rem 1rem; border-radius:8px; background:#f0fdf4; border:1px solid #bbf7d0;">
            <span style="font-weight:600; font-size:.9rem;">Total %</span>
            <span id="totalPct" style="font-weight:700; color:#059669; font-size:.95rem;">0.00 %</span>
          </div>

          <div style="margin-top:1.5rem; display:flex; gap:.75rem;">
            <button type="submit" class="btn-primary" style="padding:.6rem 2rem;">
              <i class='bx bx-check'></i> Generar Letras
            </button>
            <a href="{{ route('facturador.compras.show', $purchase) }}" class="btn-secondary" style="padding:.6rem 1.25rem;">
              Cancelar
            </a>
          </div>

        </form>
      </div>
    </div>

    {{-- Panel con letras existentes --}}
    @if($purchase->letras->count())
      <div style="background:#fff; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,.03);">
        <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--clr-border-light,rgba(0,0,0,.06)); font-weight:600; font-size:.92rem; display:flex; align-items:center; gap:.5rem;">
          <i class='bx bx-file'></i> Letras existentes
        </div>
        <table class="module-table" style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="padding:.6rem 1rem; font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase;">N°</th>
              <th style="padding:.6rem 1rem; font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase;">Venc.</th>
              <th style="padding:.6rem 1rem; font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; text-align:right;">Monto</th>
              <th style="padding:.6rem 1rem; font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase;">Estado</th>
            </tr>
          </thead>
          <tbody>
            @foreach($purchase->letras as $l)
              <tr style="border-top:1px solid var(--clr-border-light,#f3f4f6);">
                <td style="padding:.6rem 1rem; font-size:.85rem;">
                  <a href="{{ route('facturador.letras.show', $l) }}" style="color:var(--clr-active-bg,#1a6b57); font-weight:600; text-decoration:none;">{{ $l->numero_letra }}</a>
                </td>
                <td style="padding:.6rem 1rem; font-size:.82rem;">{{ $l->fecha_vencimiento->format('d/m/Y') }}</td>
                <td style="padding:.6rem 1rem; font-size:.82rem; text-align:right; font-weight:600;">{{ number_format($l->monto, 2) }}</td>
                <td style="padding:.6rem 1rem;">
                  <span class="letra-badge badge-{{ $l->estado }}">{{ $l->estado_label }}</span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

  </div>

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}
@endsection

@push('scripts')
<script>
  const montoNeto = {{ ($purchase->monto_total - ($purchase->monto_detraccion ?? 0) - ($purchase->monto_retencion ?? 0)) }};
  let cuotaCount  = 0;

  function addCuota(dias, pct) {
    cuotaCount++;
    const i   = cuotaCount;
    const row = document.createElement('div');
    row.className = 'cuota-row';
    row.id = 'cuota-' + i;
    row.innerHTML = `
      <div>
        <input type="number" name="cuotas[${i}][dias]" value="${dias || 30}"
               min="1" max="1080" required onchange="recalcMonto(${i})"
               style="width:100%; padding:.5rem .75rem; border:1px solid #e2e8f0; border-radius:8px; font-size:.9rem; outline:none; font-family:inherit; box-sizing:border-box;">
      </div>
      <div>
        <input type="number" name="cuotas[${i}][porcentaje]" class="pct-input"
               value="${pct ? pct.toFixed(2) : ''}" step="0.01" min="0.01" max="100"
               required oninput="updateTotals()"
               style="width:100%; padding:.5rem .75rem; border:1px solid #e2e8f0; border-radius:8px; font-size:.9rem; outline:none; font-family:inherit; box-sizing:border-box;">
      </div>
      <div>
        <div id="monto-${i}" style="padding:.5rem .75rem; border:1px solid #e2e8f0; border-radius:8px; font-size:.9rem; background:#f8fafc; color:#6b7280;">—</div>
      </div>
      <div>
        <button type="button" onclick="removeCuota(${i})"
                style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:8px; background:rgba(239,68,68,.1); color:#dc2626; border:1px solid rgba(239,68,68,.25); cursor:pointer; font-size:1.1rem;">
          <i class='bx bx-trash'></i>
        </button>
      </div>
    `;
    document.getElementById('cuotasContainer').appendChild(row);
    updateTotals();
  }

  function removeCuota(i) {
    document.getElementById('cuota-' + i)?.remove();
    updateTotals();
  }

  function updateTotals() {
    const inputs = document.querySelectorAll('.pct-input');
    let total = 0;
    inputs.forEach((inp, idx) => {
      const pct   = parseFloat(inp.value) || 0;
      const monto = montoNeto * pct / 100;
      total += pct;
      const container = inp.closest('.cuota-row');
      const montoEl   = container?.querySelector('[id^="monto-"]');
      if (montoEl) montoEl.textContent = 'S/ ' + monto.toFixed(2);
    });
    const el = document.getElementById('totalPct');
    el.textContent  = total.toFixed(2) + ' %';
    el.style.color  = Math.abs(total - 100) < 0.05 ? '#059669' : '#dc2626';
  }

  function recalcMonto(i) { updateTotals(); }

  // Presets
  document.getElementById('btnPresetUna').addEventListener('click', () => {
    document.getElementById('cuotasContainer').innerHTML = '';
    cuotaCount = 0;
    addCuota(30, 100);
  });
  document.getElementById('btnPreset2').addEventListener('click', () => {
    document.getElementById('cuotasContainer').innerHTML = '';
    cuotaCount = 0;
    addCuota(30, 50); addCuota(60, 50);
  });
  document.getElementById('btnPreset3').addEventListener('click', () => {
    document.getElementById('cuotasContainer').innerHTML = '';
    cuotaCount = 0;
    addCuota(30, 33.33); addCuota(60, 33.33); addCuota(90, 33.34);
  });
  document.getElementById('btnAddCuota').addEventListener('click', () => addCuota(30, null));

  // Iniciar con 1 cuota al 100%
  addCuota(30, 100);

  // Validar antes de enviar
  document.getElementById('formCanje').addEventListener('submit', function(e) {
    const total = Array.from(document.querySelectorAll('.pct-input'))
                       .reduce((s, i) => s + (parseFloat(i.value) || 0), 0);
    if (Math.abs(total - 100) > 0.1) {
      e.preventDefault();
      Swal.fire({icon:'warning', title:'Porcentajes incorrectos', text:'Los porcentajes deben sumar exactamente 100%. Suma actual: ' + total.toFixed(2) + '%'});
    }
  });
</script>
@endpush
