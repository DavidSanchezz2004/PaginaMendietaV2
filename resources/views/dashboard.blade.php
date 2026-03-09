@extends('layouts.app')

@section('title', 'Dashboard | Portal Mendieta')

@push('styles')
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        .metric-cards {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;
        }
        .metric-card {
            background: var(--clr-bg-card, #fff); border-radius: 1rem; padding: 1.5rem; display: flex; align-items: center; gap: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03); border: 1px solid var(--clr-border-light, #f3f4f6); transition: transform 0.2s;
        }
        .metric-card:hover { transform: translateY(-3px); }
        body.dark-mode .metric-card { background: var(--clr-bg-card); border-color: var(--clr-border-light); }

        .metric-icon {
            width: 56px; height: 56px; border-radius: 1rem; display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem; flex-shrink: 0;
        }
        .metric-icon.blue { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .metric-icon.red { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .metric-icon.green { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .metric-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
        
        .metric-info h3 { font-size: 0.875rem; color: var(--clr-text-muted, #6b7280); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
        .metric-info p { font-size: 1.75rem; color: var(--clr-text-main, #111827); font-weight: 800; line-height: 1; }

        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
        @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }
        
        .dash-section { background: var(--clr-bg-card, #fff); border-radius: 1rem; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03); border: 1px solid var(--clr-border-light, #f3f4f6); overflow: hidden; display: flex; flex-direction: column; }
        body.dark-mode .dash-section { background: var(--clr-bg-card); border-color: var(--clr-border-light); }
        
        .dash-section-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--clr-border-light, #f3f4f6); display: flex; justify-content: space-between; align-items: center; background: transparent; }
        .dash-section-header h2 { font-size: 1.125rem; font-weight: 700; color: var(--clr-text-main, #111827); display: flex; align-items: center; gap: 0.5rem; }
        .dash-section-body { padding: 1.5rem; flex-grow: 1; }
        
        .activity-list { display: flex; flex-direction: column; gap: 1rem; }
        .activity-item { display: flex; gap: 1rem; align-items: flex-start; padding-bottom: 1rem; border-bottom: 1px solid var(--clr-border-light, #f3f4f6); text-decoration: none; transition: background 0.2s; border-radius: 0.5rem; padding: 0.75rem; margin: -0.75rem; }
        .activity-item:hover { background: var(--clr-hover-bg, #f9fafb); }
        .activity-item:last-child { border-bottom: none; }
        
        .activity-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
        .activity-content { flex-grow: 1; }
        .activity-title { font-size: 0.95rem; font-weight: 600; color: var(--clr-text-main, #111827); margin-bottom: 0.25rem; }
        .activity-meta { font-size: 0.8rem; color: var(--clr-text-muted, #6b7280); display: flex; gap: 1rem; }
        
        .news-banner {
            background: linear-gradient(to right, #1e3a8a, #3b82f6); color: white; border-radius: 1rem; padding: 2rem;
            position: relative; overflow: hidden; margin-bottom: 2rem; display: flex; align-items: center; gap: 2rem;
        }
        .news-banner::after { content: ''; position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; }
        .news-banner-content { position: relative; z-index: 10; flex-grow: 1; }
        .news-banner-label { display: inline-block; padding: 0.25rem 0.75rem; background: rgba(255,255,255,0.2); border-radius: 999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.05em; backdrop-filter: blur(4px); }
        .news-banner h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.3; }
        .news-banner p { font-size: 0.95rem; opacity: 0.9; margin-bottom: 1.5rem; max-width: 600px; }
    </style>
@endpush

@section('content')
  <div class="app-layout">
    <aside class="sidebar-premium">
      <div class="sidebar-header">
        <img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta" class="header-logo">
        <div class="header-text">
          <h2>Portal Mendieta</h2>
          <p>Panel interno</p>
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
            
            @if($latestNews)
            <div class="news-banner">
                <div class="news-banner-content">
                    <span class="news-banner-label"><i class='bx bx-news' style="margin-right: 0.25rem;"></i> Última Novedad</span>
                    <h2>{{ $latestNews->title }}</h2>
                    <p>{{ Str::limit($latestNews->excerpt ?? strip_tags($latestNews->content), 120) }}</p>
                    <a href="{{ route('news.show', $latestNews) }}" class="btn-primary" style="background: #fff; color: #1e3a8a; border-color: #fff; padding: 0.5rem 1.5rem;">
                        Leer Anuncio <i class='bx bx-right-arrow-alt'></i>
                    </a>
                </div>
                @if($latestNews->image_path)
                    <div style="width: 250px; height: 160px; border-radius: 0.75rem; overflow: hidden; flex-shrink: 0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3); z-index: 10; display: none; @media(min-width: 768px) { display: block; }">
                        <img src="{{ Storage::url($latestNews->image_path) }}" alt="News Image" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                @endif
            </div>
            @endif

            <div class="page-header simple-header" style="margin-bottom: 1.5rem; padding-bottom: 0; border: none;">
                <div>
                    <h1 class="page-title">Vista General</h1>
                    <p class="page-description" style="color: var(--clr-text-muted, #6b7280);">Resumen de tu actividad y métricas clave en el Portal.</p>
                </div>
            </div>

            <div class="metric-cards">
                @php
                    $userRole = auth()->user()->role instanceof \App\Enums\RoleEnum ? auth()->user()->role->value : auth()->user()->role;
                    $isGlobalPanel = in_array($userRole, ['admin', 'supervisor']);
                @endphp

                @if($isGlobalPanel)
                    <!-- Admin Metrics -->
                    <div class="metric-card">
                        <div class="metric-icon blue"><i class='bx bx-buildings'></i></div>
                        <div class="metric-info">
                            <h3>Total Empresas</h3>
                            <p>{{ $metrics['total_companies'] }}</p>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon purple"><i class='bx bx-file'></i></div>
                        <div class="metric-info">
                            <h3>Reportes Emitidos</h3>
                            <p>{{ $metrics['total_reports'] }}</p>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon red"><i class='bx bx-message-square-error'></i></div>
                        <div class="metric-info">
                            <h3>Consultas Abiertas</h3>
                            <p>{{ $metrics['open_tickets'] }}</p>
                        </div>
                    </div>
                @else
                    <!-- Auxiliar/Client Metrics -->
                    @if($userRole === 'auxiliar')
                        <div class="metric-card">
                            <div class="metric-icon blue"><i class='bx bx-buildings'></i></div>
                            <div class="metric-info">
                                <h3>Empresas Asignadas</h3>
                                <p>{{ $metrics['total_companies'] }}</p>
                            </div>
                        </div>
                    @else
                        <div class="metric-card">
                            <div class="metric-icon red"><i class='bx bx-bell'></i></div>
                            <div class="metric-info">
                                <h3>Reportes Sin Leer</h3>
                                <p>{{ $metrics['unread_reports'] }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="metric-card">
                        <div class="metric-icon purple"><i class='bx bx-file'></i></div>
                        <div class="metric-info">
                            <h3>Total Reportes</h3>
                            <p>{{ $metrics['total_reports'] }}</p>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon blue"><i class='bx bx-conversation'></i></div>
                        <div class="metric-info">
                            <h3>Consultas Activas</h3>
                            <p>{{ $metrics['open_tickets'] }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="dashboard-grid">
                
                <!-- Recent Reports Feed -->
                <div class="dash-section">
                    <div class="dash-section-header">
                        <h2><i class='bx bx-folder-open' style="color: var(--clr-text-muted, #6b7280);"></i> Documentos y Reportes Recientes</h2>
                        <a href="{{ route('reports.index') }}" style="font-size: 0.85rem; font-weight: 500; color: #3b82f6; text-decoration: none;">Ver todos</a>
                    </div>
                    <div class="dash-section-body" style="padding: 1rem;">
                        <div class="activity-list">
                            @forelse($recentReports as $report)
                                <a href="javascript:void(0)" class="activity-item" style="cursor: default;">
                                    <div class="activity-icon" style="background: rgba(107, 114, 128, 0.1); color: var(--clr-text-muted, #4b5563);">
                                        @if($report->format === 'pdf')
                                            <i class='bx bxs-file-pdf' style="color: #ef4444;"></i>
                                        @elseif(in_array($report->format, ['excel', 'csv']))
                                            <i class='bx bxs-file-blank' style="color: #10b981;"></i>
                                        @else
                                            <i class='bx bx-bar-chart-alt-2' style="color: #f59e0b;"></i>
                                        @endif
                                    </div>
                                    <div class="activity-content">
                                        <h4 class="activity-title">{{ $report->title }}</h4>
                                        <div class="activity-meta">
                                            @if($isGlobalPanel || $userRole === 'auxiliar')
                                                <span><i class='bx bx-building'></i> {{ $report->company->name }}</span>
                                            @endif
                                            <span><i class='bx bx-calendar'></i> {{ $report->created_at->format('d/m/Y') }}</span>
                                            <span style="font-weight: 500; color: {{ $report->status === 'published' ? '#059669' : '#d97706' }}">{{ ucfirst($report->status) }}</span>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div style="text-align: center; padding: 2rem 0; color: var(--clr-text-muted, #6b7280);">
                                    <p>No hay reportes recientes disponibles.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Recent Tickets / Quick Actions -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    
                    <!-- Quick Actions -->
                    <div class="dash-section">
                        <div class="dash-section-header">
                            <h2><i class='bx bx-bolt-circle' style="color: #f59e0b;"></i> Accesos Rápidos</h2>
                        </div>
                        <div class="dash-section-body" style="display: flex; flex-direction: column; gap: 0.75rem; padding: 1.25rem;">
                            @can('create', App\Models\Ticket::class)
                            <a href="{{ route('tickets.create') }}" class="btn-primary" style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; padding: 0.75rem;">
                                <i class='bx bx-pencil'></i> Nueva Consulta
                            </a>
                            @endcan
                            @can('viewAny', App\Models\FinalDocument::class)
                            <a href="{{ route('final-documents.index') }}" class="btn-secondary" style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; padding: 0.75rem;">
                                <i class='bx bx-folder'></i> Ver Docs. Finales
                            </a>
                            @endcan
                            @can('create', App\Models\Report::class)
                                <a href="{{ route('reports.create') }}" class="btn-secondary" style="display: flex; justify-content: center; align-items: center; /* background: #fff inline estropeaba modo oscuro */ padding: 0.75rem;">
                                    <i class='bx bx-cloud-upload'></i> Subir Reporte
                                </a>
                            @endcan
                        </div>
                    </div>

                    <!-- Tickets -->
                    <div class="dash-section">
                        <div class="dash-section-header">
                            <h2><i class='bx bx-message-square-detail' style="color: var(--clr-text-muted, #6b7280);"></i> Actividad de Soporte</h2>
                        </div>
                        <div class="dash-section-body" style="padding: 1rem;">
                            <div class="activity-list">
                                @forelse($recentTickets as $ticket)
                                    <a href="{{ route('tickets.show', $ticket) }}" class="activity-item" style="align-items: center;">
                                        <div class="activity-icon" style="background: {{ $ticket->status->value === 'open' ? 'rgba(239, 68, 68, 0.1)' : ($ticket->status->value === 'in_progress' ? 'rgba(59, 130, 246, 0.1)' : 'rgba(107, 114, 128, 0.1)') }}; color: {{ $ticket->status->value === 'open' ? '#dc2626' : ($ticket->status->value === 'in_progress' ? '#3b82f6' : 'var(--clr-text-muted)') }};">
                                            @if($ticket->status->value === 'open')
                                                <i class='bx bx-envelope'></i>
                                            @elseif($ticket->status->value === 'in_progress')
                                                <i class='bx bx-envelope-open'></i>
                                            @else
                                                <i class='bx bx-check-double'></i>
                                            @endif
                                        </div>
                                        <div class="activity-content">
                                            <h4 class="activity-title" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">{{ $ticket->subject }}</h4>
                                            <div class="activity-meta">
                                                <span>{{ $ticket->updated_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div style="text-align: center; padding: 1.5rem 0; color: var(--clr-text-muted, #6b7280); font-size: 0.85rem;">
                                        <p>No hay actividad de soporte reciente.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
  <script>
    // Scripts específicos del Dashboard (si los hay) pueden ir aquí
  </script>
@endpush
