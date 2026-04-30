@extends('layouts.app')

@section('title', 'Validar comprobante SUNAT | Portal Mendieta')

@section('content')
@component('sunat.comprobantes._layout', ['company' => $company])
  @if(! $credential || ! $credential->is_active)
    <div class="sunat-alert">
      Esta empresa no tiene credenciales API SUNAT activas. Configúralas antes de validar comprobantes.
      <a href="{{ route('sunat.comprobantes.credenciales') }}" style="font-weight:800; color:inherit;">Ir a credenciales</a>
    </div>
  @endif

  <form method="POST" action="{{ route('sunat.comprobantes.validar') }}">
    @csrf
    <input type="hidden" name="empresa_id" value="{{ $company->id }}">
    @if(!empty($prefill))
      <div class="sunat-alert" style="margin-bottom:1rem;">
        Datos cargados desde {{ $prefill['serie_numero'] ?? 'comprobante' }}
        @if(!empty($prefill['client_name']))
          · {{ $prefill['client_name'] }} {{ !empty($prefill['client_document']) ? '(' . $prefill['client_document'] . ')' : '' }}
        @endif
      </div>
    @endif

    <div class="sunat-form-grid">
      <div class="sunat-field">
        <label>RUC emisor</label>
        <input type="text" name="numRuc" value="{{ old('numRuc', $prefill['numRuc'] ?? '') }}" maxlength="11" pattern="[0-9]{11}" required>
        @error('numRuc')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="sunat-field">
        <label>Tipo comprobante</label>
        <select name="codComp" required>
          @foreach(['01'=>'Factura','03'=>'Boleta de venta','04'=>'Liquidación de compra','07'=>'Nota de crédito','08'=>'Nota de débito','R1'=>'Recibo por honorarios','R7'=>'Nota crédito RH'] as $code => $label)
            <option value="{{ $code }}" @selected(old('codComp', $prefill['codComp'] ?? '01') === $code)>{{ $code }} - {{ $label }}</option>
          @endforeach
        </select>
        @error('codComp')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="sunat-field">
        <label>Serie</label>
        <input type="text" name="numeroSerie" value="{{ old('numeroSerie', $prefill['numeroSerie'] ?? '') }}" maxlength="4" required>
        @error('numeroSerie')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="sunat-field">
        <label>Número</label>
        <input type="number" name="numero" value="{{ old('numero', $prefill['numero'] ?? '') }}" min="1" max="99999999" required>
        @error('numero')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="sunat-field">
        <label>Fecha emisión</label>
        <input type="text" name="fechaEmision" value="{{ old('fechaEmision', $prefill['fechaEmision'] ?? now()->format('d/m/Y')) }}" placeholder="dd/mm/aaaa" required>
        @error('fechaEmision')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="sunat-field">
        <label>Monto total</label>
        <input type="number" name="monto" value="{{ old('monto', $prefill['monto'] ?? '') }}" min="0" step="0.01">
        <div class="sunat-help">Obligatorio para comprobantes electrónicos.</div>
        @error('monto')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    <div class="sunat-actions">
      <a href="{{ route('sunat.comprobantes.historial') }}" class="btn-secondary"><i class='bx bx-history'></i> Ver historial</a>
      <button type="submit" class="btn-primary" @disabled(! $credential || ! $credential->is_active)>
        <i class='bx bx-search-alt'></i> Validar en SUNAT
      </button>
    </div>
  </form>

  @if($lastValidation)
    @include('sunat.comprobantes._resultado', ['validacion' => $lastValidation])
  @endif
@endcomponent
@endsection
