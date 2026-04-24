@if($purchase->gre_numero)
<div class="detail-section">
  <div class="detail-section__title">
    <i class='bx bx-map'></i> Datos de Guía de Remisión (GRE)
  </div>
  
  <div class="dl-row">
    <span class="dl-label">Número GRE</span>
    <span class="dl-value">{{ $purchase->gre_numero }}</span>
  </div>
  
  @if($purchase->gre_fecha_inicio_traslado)
  <div class="dl-row">
    <span class="dl-label">Fecha Inicio Traslado</span>
    <span class="dl-value">{{ $purchase->gre_fecha_inicio_traslado->format('d/m/Y') }}</span>
  </div>
  @endif
  
  @if($purchase->gre_motivo_traslado)
  <div class="dl-row">
    <span class="dl-label">Motivo de Traslado</span>
    <span class="dl-value">{{ $purchase->gre_motivo_traslado }}</span>
  </div>
  @endif
  
  @if($purchase->gre_punto_partida)
  <div class="dl-row">
    <span class="dl-label">Punto de Partida</span>
    <span class="dl-value" style="white-space: pre-wrap;">{{ $purchase->gre_punto_partida }}</span>
  </div>
  @endif
  
  @if($purchase->gre_punto_llegada)
  <div class="dl-row">
    <span class="dl-label">Punto de Llegada</span>
    <span class="dl-value" style="white-space: pre-wrap;">{{ $purchase->gre_punto_llegada }}</span>
  </div>
  @endif
  
  @if($purchase->gre_destinatario_ruc)
  <div class="dl-row">
    <span class="dl-label">RUC Destinatario</span>
    <span class="dl-value">{{ $purchase->gre_destinatario_ruc }}</span>
  </div>
  @endif
  
  @if($purchase->gre_destinatario_razon_social)
  <div class="dl-row">
    <span class="dl-label">Razón Social Destinatario</span>
    <span class="dl-value">{{ $purchase->gre_destinatario_razon_social }}</span>
  </div>
  @endif
  
  @if($purchase->gre_documento_relacionado)
  <div class="dl-row">
    <span class="dl-label">Documento Relacionado</span>
    <span class="dl-value">{{ $purchase->gre_documento_relacionado }}</span>
  </div>
  @endif
  
  @if($purchase->gre_bienes_descripcion)
  <div class="dl-row">
    <span class="dl-label">Descripción de Bienes</span>
    <span class="dl-value" style="white-space: pre-wrap;">{{ $purchase->gre_bienes_descripcion }}</span>
  </div>
  @endif
  
  @if($purchase->gre_cantidad_bienes)
  <div class="dl-row">
    <span class="dl-label">Cantidad</span>
    <span class="dl-value">{{ $purchase->gre_cantidad_bienes }} {{ $purchase->gre_unidad_medida ?? '' }}</span>
  </div>
  @endif
  
  @if($purchase->gre_peso_bruto)
  <div class="dl-row">
    <span class="dl-label">Peso Bruto Total</span>
    <span class="dl-value">{{ $purchase->gre_peso_bruto }} {{ $purchase->gre_unidad_medida_peso ?? 'KGM' }}</span>
  </div>
  @endif
  
  @if($purchase->gre_datos_vehiculo)
  <div class="dl-row">
    <span class="dl-label">Placa Vehículo</span>
    <span class="dl-value">{{ is_array($purchase->gre_datos_vehiculo) ? ($purchase->gre_datos_vehiculo['placa'] ?? '—') : ($purchase->gre_datos_vehiculo ?: '—') }}</span>
  </div>
  @endif

  @php
    $conductor = is_array($purchase->gre_datos_conductor) ? $purchase->gre_datos_conductor : [];
    $nombreConductor  = $conductor['nombre']         ?? null;
    $dniConductor     = $conductor['dni']             ?? null;
    $licenciaConductor = $conductor['numero_licencia'] ?? null;
  @endphp
  @if(!empty($conductor))
  <div class="dl-row">
    <span class="dl-label">Conductor</span>
    <span class="dl-value">
      {{ $nombreConductor ?? '—' }}
      @if($dniConductor)
        (DNI: {{ $dniConductor }})
      @endif
    </span>
  </div>
  @if($licenciaConductor)
  <div class="dl-row">
    <span class="dl-label">Licencia de Conducir</span>
    <span class="dl-value">{{ $licenciaConductor }}</span>
  </div>
  @endif
  @endif
  
  @if($purchase->gre_privado_transporte)
  <div class="dl-row">
    <span class="dl-label">Transporte</span>
    <span class="dl-value" style="color: #059669; font-weight: 600;">Privado</span>
  </div>
  @endif
  
  @if($purchase->gre_retorno_vehiculo_vacio)
  <div class="dl-row">
    <span class="dl-label">Retorno de Vehículo</span>
    <span class="dl-value">Sí, vacío</span>
  </div>
  @endif
  
  @if($purchase->gre_notas)
  <div class="dl-row">
    <span class="dl-label">Notas</span>
    <span class="dl-value" style="white-space: pre-wrap;">{{ $purchase->gre_notas }}</span>
  </div>
  @endif
  
  @if($purchase->gre_registrado_en)
  <div class="dl-row">
    <span class="dl-label">Registrado</span>
    <span class="dl-value">{{ $purchase->gre_registrado_en->format('d/m/Y H:i') }}</span>
  </div>
  @endif
</div>
@endif
