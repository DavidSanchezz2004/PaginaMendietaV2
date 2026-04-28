@extends('layouts.app')

@section('title', 'Historial SUNAT | Portal Mendieta')

@section('content')
@component('sunat.comprobantes._layout', ['company' => $company])
  <form method="GET" class="sunat-form-grid" style="margin-bottom:1rem;">
    <div class="sunat-field">
      <label>Desde</label>
      <input type="date" name="from" value="{{ $filters['from'] ?? '' }}">
    </div>
    <div class="sunat-field">
      <label>Hasta</label>
      <input type="date" name="to" value="{{ $filters['to'] ?? '' }}">
    </div>
    <div class="sunat-field">
      <label>RUC emisor</label>
      <input type="text" name="num_ruc_emisor" value="{{ $filters['num_ruc_emisor'] ?? '' }}" maxlength="11">
    </div>
    <div class="sunat-field">
      <label>Tipo</label>
      <select name="cod_comp">
        <option value="">Todos</option>
        @foreach(['01'=>'Factura','03'=>'Boleta','04'=>'Liquidación','07'=>'N. Crédito','08'=>'N. Débito','R1'=>'RH','R7'=>'NC RH'] as $code => $label)
          <option value="{{ $code }}" @selected(($filters['cod_comp'] ?? '') === $code)>{{ $code }} - {{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="sunat-field">
      <label>Estado CP</label>
      <select name="estado_cp">
        <option value="">Todos</option>
        @foreach(['0'=>'No existe','1'=>'Aceptado','2'=>'Anulado','3'=>'Autorizado','4'=>'No autorizado'] as $code => $label)
          <option value="{{ $code }}" @selected(($filters['estado_cp'] ?? '') === $code)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="sunat-actions" style="align-items:end; margin-top:0;">
      <button type="submit" class="btn-primary"><i class='bx bx-filter-alt'></i> Filtrar</button>
      <a href="{{ route('sunat.comprobantes.historial') }}" class="btn-secondary">Limpiar</a>
    </div>
  </form>

  <div class="sunat-table-wrap">
    <table class="sunat-table">
      <thead>
        <tr>
          <th>Fecha consulta</th>
          <th>Usuario</th>
          <th>RUC emisor</th>
          <th>Comprobante</th>
          <th>Emisión</th>
          <th>Monto</th>
          <th>Estado CP</th>
          <th>RUC</th>
          <th>Domicilio</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($validaciones as $validacion)
          <tr>
            <td>{{ $validacion->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $validacion->user?->name ?? '—' }}</td>
            <td>{{ $validacion->num_ruc_emisor }}</td>
            <td><code>{{ $validacion->cod_comp }} {{ $validacion->numero_serie }}-{{ $validacion->numero }}</code></td>
            <td>{{ $validacion->fecha_emision->format('d/m/Y') }}</td>
            <td>{{ $validacion->monto !== null ? number_format((float) $validacion->monto, 2) : '—' }}</td>
            <td><span class="sunat-badge {{ $validacion->estado_cp === '1' ? 'green' : (in_array($validacion->estado_cp, ['2','4'], true) ? 'red' : ($validacion->estado_cp === '3' ? 'blue' : 'gray')) }}">{{ $validacion->estado_cp_texto ?? '—' }}</span></td>
            <td><span class="sunat-badge {{ $validacion->estado_ruc === '00' ? 'green' : 'yellow' }}">{{ $validacion->estado_ruc_texto ?? '—' }}</span></td>
            <td><span class="sunat-badge {{ $validacion->cond_domi_ruc === '00' ? 'green' : ($validacion->cond_domi_ruc === '12' ? 'red' : 'yellow') }}">{{ $validacion->cond_domi_ruc_texto ?? '—' }}</span></td>
            <td><a href="{{ route('sunat.comprobantes.show', $validacion) }}" class="btn-secondary" style="padding:.35rem .6rem;">Ver</a></td>
          </tr>
        @empty
          <tr><td colspan="10">No hay consultas registradas.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:1rem;">{{ $validaciones->links() }}</div>
@endcomponent
@endsection
