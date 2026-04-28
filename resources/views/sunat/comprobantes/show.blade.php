@extends('layouts.app')

@section('title', 'Detalle consulta SUNAT | Portal Mendieta')

@section('content')
@component('sunat.comprobantes._layout', ['company' => $company])
  <div style="display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
    <div>
      <h2 style="margin:0; font-size:1.05rem;">{{ $validacion->cod_comp }} {{ $validacion->numero_serie }}-{{ $validacion->numero }}</h2>
      <p style="margin:.2rem 0 0; color:var(--clr-text-muted,#6b7280);">RUC emisor {{ $validacion->num_ruc_emisor }} · consultado por {{ $validacion->user?->name ?? '—' }}</p>
    </div>
    <a href="{{ route('sunat.comprobantes.historial') }}" class="btn-secondary"><i class='bx bx-arrow-back'></i> Historial</a>
  </div>

  @include('sunat.comprobantes._resultado', ['validacion' => $validacion])

  <div class="sunat-result-grid">
    <div class="sunat-result-card">
      <span>RUC consultante</span>
      <strong>{{ $validacion->ruc_consultante }}</strong>
    </div>
    <div class="sunat-result-card">
      <span>Fecha emisión</span>
      <strong>{{ $validacion->fecha_emision->format('d/m/Y') }}</strong>
    </div>
    <div class="sunat-result-card">
      <span>Monto</span>
      <strong>{{ $validacion->monto !== null ? number_format((float) $validacion->monto, 2) : '—' }}</strong>
    </div>
  </div>

  <div class="sunat-form-grid cols-2" style="margin-top:1rem;">
    <div class="sunat-field">
      <label>Request enviado</label>
      <textarea readonly>{{ json_encode($validacion->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
    </div>
    <div class="sunat-field">
      <label>Response SUNAT</label>
      <textarea readonly>{{ json_encode($validacion->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
    </div>
  </div>
@endcomponent
@endsection
