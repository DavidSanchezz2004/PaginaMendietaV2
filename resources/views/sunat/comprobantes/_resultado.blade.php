@php
  $estadoCpClass = match($validacion->estado_cp) {
    '1' => 'green',
    '2', '4' => 'red',
    '3' => 'blue',
    '0' => 'gray',
    default => 'yellow',
  };
  $estadoRucClass = $validacion->estado_ruc === '00' ? 'green' : 'yellow';
  $domClass = match($validacion->cond_domi_ruc) {
    '00' => 'green',
    '12' => 'red',
    null => 'gray',
    default => 'yellow',
  };
@endphp

<div class="sunat-result-grid">
  <div class="sunat-result-card">
    <span>Estado comprobante</span>
    <strong class="sunat-badge {{ $estadoCpClass }}">{{ $validacion->estado_cp_texto ?? 'SIN ESTADO' }}</strong>
  </div>
  <div class="sunat-result-card">
    <span>Estado RUC</span>
    <strong class="sunat-badge {{ $estadoRucClass }}">{{ $validacion->estado_ruc_texto ?? 'SIN ESTADO' }}</strong>
  </div>
  <div class="sunat-result-card">
    <span>Condición domicilio</span>
    <strong class="sunat-badge {{ $domClass }}">{{ $validacion->cond_domi_ruc_texto ?? 'SIN ESTADO' }}</strong>
  </div>
</div>

@if(!empty($validacion->observaciones))
  <div style="margin-top:1rem;">
    <h3 style="font-size:.95rem; margin:0 0 .5rem;">Observaciones</h3>
    <div class="sunat-table-wrap">
      <table class="sunat-table">
        <tbody>
          @foreach($validacion->observaciones as $observacion)
            <tr><td>{{ is_scalar($observacion) ? $observacion : json_encode($observacion) }}</td></tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif
