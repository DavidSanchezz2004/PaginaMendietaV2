@extends('layouts.app')

@section('title', 'Credenciales API SUNAT | Portal Mendieta')

@section('content')
@component('sunat.comprobantes._layout', ['company' => $company])
  <form method="POST" action="{{ route('sunat.comprobantes.credenciales.store') }}">
    @csrf
    <input type="hidden" name="empresa_id" value="{{ $company->id }}">

    <div class="sunat-form-grid cols-2">
      <div class="sunat-field">
        <label>RUC consultante</label>
        <input type="text" name="ruc_consultante" value="{{ old('ruc_consultante', $credential->ruc_consultante ?? $company->ruc) }}" maxlength="11" required>
        @error('ruc_consultante')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="sunat-field">
        <label>Estado integración</label>
        <select name="is_active">
          <option value="1" @selected(old('is_active', $credential->is_active ?? true))>Activa</option>
          <option value="0" @selected(! old('is_active', $credential->is_active ?? true))>Inactiva</option>
        </select>
      </div>
      <div class="sunat-field">
        <label>Client ID</label>
        <input type="text" name="client_id" value="{{ old('client_id', $credential->client_id ?? '') }}" required>
        @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="sunat-field">
        <label>Client Secret</label>
        <input type="password" name="client_secret" value="" placeholder="{{ $credential ? '******** guardado' : 'Ingresa client_secret' }}">
        <div class="sunat-help">Si ya está guardado, déjalo vacío para conservarlo.</div>
        @error('client_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    @if($credential?->last_token_generated_at)
      <div class="sunat-alert success" style="margin-top:1rem;">Último token generado: {{ $credential->last_token_generated_at->format('d/m/Y H:i') }}</div>
    @endif
    @if($credential?->last_error)
      <div class="sunat-alert error" style="margin-top:1rem;">Último error: {{ $credential->last_error }}</div>
    @endif

    <div class="sunat-alert" style="margin-top:1rem;">
      Para que SUNAT entregue token, el <strong>client_id</strong> y <strong>client_secret</strong> deben ser los generados en Menú SOL para el mismo RUC consultante mostrado arriba. Si SUNAT responde <strong>cliente no autorizado</strong>, revisa que la credencial API esté activa y habilitada para Consulta Integrada de Comprobante de Pago.
    </div>

    <div class="sunat-actions">
      <button type="submit" class="btn-primary"><i class='bx bx-save'></i> Guardar</button>
    </div>
  </form>

  @if($credential)
    <form method="POST" action="{{ route('sunat.comprobantes.probar') }}" class="sunat-actions" style="justify-content:flex-start;">
      @csrf
      <button type="submit" class="btn-secondary"><i class='bx bx-plug'></i> Probar conexión</button>
    </form>
  @endif
@endcomponent
@endsection
