@extends('layouts.app')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')

<div class="app-layout">
    <aside class="sidebar-premium">
        <div class="sidebar-header">
            <img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta" class="header-logo">
            <div class="header-text">
                <h2>Portal Mendieta</h2>
                <p>Panel Administrativo</p>
            </div>
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
            'userName' => auth()->user()?->name,
            'userEmail' => auth()->user()?->email,
        ])

        <main class="main-content">
            <div class="module-content-stack">
                <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
                    <div class="dashboard-title">
                        <h2 style="color: var(--clr-text-main, #111827); font-size: 1.5rem; font-weight: 800;">Calendario de Obligaciones</h2>
                        <p style="color: var(--clr-text-muted, #6b7280);">Control de vencimientos y agenda impositiva</p>
                    </div>
                    @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                        <a href="{{ route('obligations.create') }}" class="btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: 0.75rem;">
                            <i class='bx bx-calendar-plus'></i> Nueva Obligación
                        </a>
                    @endif
                </div>

                @if(session('status'))
                    <div class="alert alert-success" style="background-color: #ecfdf5; color: #065f46; padding: 1rem; border-radius: 0.75rem; border: 1px solid #a7f3d0; margin-bottom: 1.5rem;">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="dashboard-card" style="margin-bottom: 2rem; padding: 2rem; border-radius: 1.25rem;">
                    <!-- Controles del Calendario -->
                    <div class="calendar-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1.5rem;">
                        
                        <!-- Paginación Mensual -->
                        <div class="month-navigation" style="display: flex; align-items: center; gap: 1.25rem; background: var(--clr-bg-card, #f9fafb); padding: 0.5rem 0.75rem; border-radius: 1rem; border: 1px solid var(--clr-border-light, #f3f4f6);">
                            @php
                                $prevMonth = $date->copy()->subMonth();
                                $nextMonth = $date->copy()->addMonth();
                            @endphp
                            <a href="{{ route('obligations.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year, 'company_id' => request('company_id')]) }}" class="btn-nav" style="color: var(--clr-text-muted, #6b7280); font-size: 1.5rem; display: flex; transition: color 0.2s;">
                                <i class='bx bx-chevron-left'></i>
                            </a>
                            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--clr-text-main); min-width: 160px; text-align: center; font-family: 'Plus Jakarta Sans', sans-serif;">
                                {{ ucfirst($date->translatedFormat('F Y')) }}
                            </h3>
                            <a href="{{ route('obligations.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year, 'company_id' => request('company_id')]) }}" class="btn-nav" style="color: var(--clr-text-muted, #6b7280); font-size: 1.5rem; display: flex; transition: color 0.2s;">
                                <i class='bx bx-chevron-right'></i>
                            </a>
                        </div>

                        <!-- Filtros (Admin) -->
                        @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                            <form method="GET" action="{{ route('obligations.index') }}" style="display: flex; gap: 0.75rem; align-items: center;">
                                <input type="hidden" name="month" value="{{ $month }}">
                                <input type="hidden" name="year" value="{{ $year }}">
                                <div style="position: relative;">
                                    <select name="company_id" class="form-select" onchange="this.form.submit()" style="min-width: 280px; padding: 0.75rem 1rem; padding-right: 2.5rem; border-radius: 0.75rem; border: 1px solid var(--clr-border-light, #e5e7eb); background-color: var(--clr-bg-card, #fff); color: var(--clr-text-main, #111827); appearance: none; font-size: 0.9rem; font-weight: 500; cursor: pointer;">
                                        <option value="">Todas las empresas</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class='bx bx-chevron-down' style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--clr-text-muted, #9ca3af); pointer-events: none;"></i>
                                </div>
                            </form>
                        @endif
                    </div>

                    <!-- Grilla del Calendario -->
                    <div class="calendar-grid">
                        <!-- Días de la semana -->
                        <div class="calendar-header">
                            <div>Dom</div>
                            <div>Lun</div>
                            <div>Mar</div>
                            <div>Mié</div>
                            <div>Jue</div>
                            <div>Vie</div>
                            <div>Sáb</div>
                        </div>

                        <!-- Matriz de Días -->
                        <div class="calendar-body">
                            @foreach($calendar as $day)
                                <div class="calendar-day {{ !$day['isCurrentMonth'] ? 'other-month' : '' }} {{ $day['isToday'] ? 'is-today' : '' }}">
                                    <div class="day-number">{{ $day['date']->format('j') }}</div>
                                    
                                    <div class="obligations-list">
                                        @foreach($day['obligations'] as $ob)
                                            <div class="obligation-tag status-{{ $ob->status }}">
                                                <div class="ob-title" title="{{ $ob->title }}">{{ $ob->title }}</div>
                                                
                                                <div class="ob-actions">
                                                    @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                                                        <a href="{{ route('obligations.edit', $ob) }}" class="ob-btn edit" title="Editar">
                                                            <i class='bx bx-edit'></i>
                                                        </a>
                                                        @if($ob->status !== 'completed')
                                                                <form action="{{ route('obligations.complete', $ob) }}" method="POST" style="display:inline;" data-confirm="¿Marcar esta obligación como completada?">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="ob-btn complete" title="Marcar pagado">
                                                                    <i class='bx bx-check-circle'></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </section>
</div>

<style>
/* Estilos específicos del Calendario */
.calendar-grid {
    border: 1px solid var(--clr-border-light, #f3f4f6);
    border-radius: 1rem;
    overflow: hidden;
    background: var(--clr-bg-card, #fff);
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}
body.dark-mode .calendar-grid { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background-color: var(--clr-bg-body, #f9fafb);
    border-bottom: 1px solid var(--clr-border-light, #f3f4f6);
    text-align: center;
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: var(--clr-text-muted, #6b7280);
    letter-spacing: 0.05em;
    padding: 1.25rem 0;
}

.calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    grid-auto-rows: minmax(140px, auto);
}

.calendar-day {
    padding: 1rem;
    border-right: 1px solid var(--clr-border-light, #f3f4f6);
    border-bottom: 1px solid var(--clr-border-light, #f3f4f6);
    transition: background-color 0.2s;
    background-color: var(--clr-bg-card, #fff);
    display: flex;
    flex-direction: column;
}

.calendar-day:nth-child(7n) { border-right: none; }
.calendar-day.other-month { background-color: var(--clr-bg-body, #fafbfc); color: var(--clr-text-muted, #d1d5db); opacity: 0.6; }
.calendar-day.is-today { background-color: rgba(59, 130, 246, 0.05); }
.calendar-day.is-today .day-number { 
    background-color: var(--clr-active-bg, #3b82f6); 
    color: #fff; 
    width: 28px; 
    height: 28px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    border-radius: 50%;
    font-weight: 700;
}

.day-number {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
    color: var(--clr-text-main, #374151);
    align-self: flex-end;
}

.obligations-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.obligation-tag {
    padding: 0.5rem 0.75rem;
    border-radius: 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid transparent;
}

.obligation-tag:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}

.ob-title {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 5px;
    flex: 1;
}

/* Colores por estado */
.status-pending { background-color: rgba(245, 158, 11, 0.1); color: #d97706; border-color: rgba(245, 158, 11, 0.2); }
.status-completed { background-color: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2); }
.status-expired { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

.ob-actions {
    display: flex;
    gap: 0.25rem;
    opacity: 0;
    transition: opacity 0.2s;
}

.obligation-tag:hover .ob-actions {
    opacity: 1;
}

.ob-btn {
    background: var(--clr-bg-card, rgba(255,255,255,0.8));
    border: none;
    border-radius: 4px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1rem;
    color: var(--clr-text-main, inherit);
    transition: background 0.2s, opacity 0.2s;
    padding: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.ob-btn:hover { background: var(--clr-hover-bg, #fff); }
.ob-btn.complete:hover { color: #10b981; }
.ob-btn.edit:hover { color: #3b82f6; }

.btn-nav:hover {
    color: var(--clr-active-bg) !important;
}

@media (max-width: 768px) {
    .calendar-body { grid-auto-rows: minmax(100px, auto); }
    .calendar-day { padding: 0.5rem; }
    .day-number { font-size: 0.8rem; margin-bottom: 0.25rem; }
    .obligation-tag { padding: 0.3rem 0.5rem; font-size: 0.7rem; }
    .ob-actions { display: none; }
}
</style>
@endsection
