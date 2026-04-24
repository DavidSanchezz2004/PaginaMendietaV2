@extends('layouts.app')

@section('title', 'Cotizaciones | Portal Mendieta')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Cotizaciones</h1>
        <a href="{{ route('facturador.cotizaciones.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> Nueva Cotización
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Buscar..." 
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="draft" {{ request('estado') === 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="sent" {{ request('estado') === 'sent' ? 'selected' : '' }}>Enviada</option>
                        <option value="accepted" {{ request('estado') === 'accepted' ? 'selected' : '' }}>Aceptada</option>
                        <option value="rejected" {{ request('estado') === 'rejected' ? 'selected' : '' }}>Rechazada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bx bx-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de cotizaciones --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Versión</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotes as $quote)
                        <tr>
                            <td>
                                <strong>{{ $quote->numero_cotizacion }}</strong>
                                @if($quote->version > 1)
                                    <span class="badge bg-info">v{{ $quote->version }}</span>
                                @endif
                            </td>
                            <td>{{ $quote->client?->nombre_cliente ?? 'Sin cliente' }}</td>
                            <td>{{ $quote->fecha_emision?->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $quote->codigo_moneda }} {{ number_format($quote->monto_total, 2) }}
                                </span>
                            </td>
                            <td>
                                @switch($quote->estado)
                                    @case('draft')
                                        <span class="badge bg-secondary">Borrador</span>
                                        @break
                                    @case('sent')
                                        <span class="badge bg-info">Enviada</span>
                                        @break
                                    @case('accepted')
                                        <span class="badge bg-success">Aceptada</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">Rechazada</span>
                                        @break
                                @endswitch
                            </td>
                            <td>{{ $quote->version }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('facturador.cotizaciones.show', $quote) }}" 
                                        class="btn btn-sm btn-outline-primary" title="Ver">
                                        <i class="bx bx-eye"></i>
                                    </a>
                                    @if($quote->estado === 'draft')
                                        <a href="{{ route('facturador.cotizaciones.edit', $quote) }}" 
                                            class="btn btn-sm btn-outline-warning" title="Editar">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('facturador.cotizaciones.pdf', $quote) }}" 
                                        class="btn btn-sm btn-outline-secondary" title="PDF">
                                        <i class="bx bx-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No hay cotizaciones registradas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotes->hasPages())
            <div class="card-footer">
                {{ $quotes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
