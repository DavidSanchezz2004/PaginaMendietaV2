@extends('layouts.app')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
@endpush

@section('content')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

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
                <div class="dashboard-header">
                    <div class="dashboard-title">
                        <h2>Bóveda de Credenciales</h2>
                        <p>Gestión segura de accesos críticos (SUNAT, AFP, Municipalidades)</p>
                    </div>
                    @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                        <a href="{{ route('credentials.create') }}" class="btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: 0.75rem;">
                            <i class='bx bx-lock-alt'></i> Nueva Credencial
                        </a>
                    @endif
                </div>

                @if(session('status'))
                    <div class="alert alert-success" style="background-color: #ecfdf5; color: #065f46; padding: 1rem; border-radius: 0.75rem; border: 1px solid #a7f3d0; margin-bottom: 1.5rem;">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="dashboard-card" style="margin-bottom: 2rem; border-radius: 1.25rem; overflow: hidden;">
                    <div class="card-toolbar" style="padding: 1.5rem 2rem; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: #fff;">
                        <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--clr-text-main);">Listado de Accesos</h3>
                        
                        @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                            <form method="GET" action="{{ route('credentials.index') }}" style="display: flex;">
                                <div style="position: relative;">
                                    <select name="company_id" class="form-select" onchange="this.form.submit()" style="min-width: 280px; padding: 0.6rem 1rem; padding-right: 2.5rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; background-color: #f9fafb; appearance: none; font-size: 0.85rem; font-weight: 500; cursor: pointer;">
                                        <option value="">Todas las empresas</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class='bx bx-chevron-down' style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none;"></i>
                                </div>
                            </form>
                        @endif
                    </div>
                    
                    <div class="module-table-wrap">
                        <table class="module-table">
                            <thead>
                                <tr>
                                    @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                                        <th style="padding: 1.25rem 2rem;">Empresa</th>
                                    @endif
                                    <th style="padding: 1.25rem 2rem;">Plataforma</th>
                                    <th>Usuario / RUC</th>
                                    <th>Contraseña Segura</th>
                                    <th>Notas</th>
                                    @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                                        <th style="text-align: right; padding-right: 2rem;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($credentials as $cred)
                                    <tr>
                                        @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                                            <td style="padding: 1.25rem 2rem;">
                                                <div style="font-weight: 600; color: var(--clr-text-main);">{{ $cred->company->name }}</div>
                                                <div style="font-size: 0.75rem; color: #6b7280;">RUC: {{ $cred->company->ruc }}</div>
                                            </td>
                                        @endif
                                        <td style="padding: 1.25rem 2rem;">
                                            <div style="display: flex; align-items: center; gap: 0.6rem;">
                                                <div style="width: 32px; height: 32px; background: #eff6ff; color: #2563eb; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                                    <i class='bx bx-desktop'></i>
                                                </div>
                                                <span style="font-weight: 700; color: #2563eb;">{{ $cred->platform }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.5rem; background: #f9fafb; padding: 0.4rem 0.75rem; border-radius: 0.5rem; border: 1px solid #f3f4f6; width: fit-content;">
                                                <span style="font-family: monospace; font-weight: 600; color: #4b5563;">{{ $cred->username ?? '-' }}</span>
                                                @if($cred->username)
                                                    <button type="button" class="copy-btn" data-clipboard="{{ $cred->username }}" title="Copiar Usuario" style="background:none; border:none; cursor:pointer; color:#9ca3af; display: flex; transition: color 0.2s;"><i class='bx bx-copy'></i></button>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.5rem; background: #fffbeb; padding: 0.4rem 0.75rem; border-radius: 0.5rem; border: 1px solid #fef3c7; width: fit-content;">
                                                <input type="password" value="{{ $cred->password }}" readonly class="pwd-field-{{ $cred->id }}" style="border:none; outline:none; background:transparent; width: 100px; font-family: monospace; color: #92400e; font-weight: 700; font-size: 1rem;">
                                                <div style="display:flex; gap: 0.4rem; border-left: 1px solid #fde68a; padding-left: 0.4rem; margin-left: 0.2rem;">
                                                    <button type="button" onclick="togglePasswordVisibility({{ $cred->id }})" title="Mostrar/Ocultar" style="background:none; border:none; cursor:pointer; color:#b45309; display: flex;"><i class='bx bx-show eye-icon-{{ $cred->id }}'></i></button>
                                                    <button type="button" class="copy-btn" data-clipboard="{{ $cred->password }}" title="Copiar Contraseña" style="background:none; border:none; cursor:pointer; color:#b45309; display: flex;"><i class='bx bx-copy'></i></button>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="color: #6b7280; font-size: 0.85rem; max-width: 200px;">
                                            {{ $cred->notes ? Str::limit($cred->notes, 60) : 'Sin notas' }}
                                        </td>
                                        
                                        @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                                            <td style="text-align: right; padding-right: 2rem;">
                                                <a href="{{ route('credentials.edit', $cred) }}" class="btn-action-edit" style="color: #6b7280; text-decoration: none; padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600; font-size: 0.85rem;">
                                                    <i class='bx bx-edit-alt'></i> Gestionar
                                                </a>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ (auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR) ? 6 : 4 }}" style="text-align: center; padding: 4rem; color: #9ca3af;">
                                            <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"><i class='bx bx-lock-open-alt'></i></div>
                                            <p style="font-weight: 500;">No se encontraron credenciales en la bóveda.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </section>
</div>

<style>
    .btn-action-edit:hover {
        background-color: #f3f4f6;
        color: var(--clr-active-bg) !important;
    }
    .copy-btn:hover {
        color: #4b5563 !important;
    }
    .module-table thead th {
        background: #f9fafb;
        color: #6b7280;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.75rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .module-table tbody tr {
        transition: background 0.2s;
    }
    .module-table tbody tr:hover {
        background-color: #fcfdfe;
    }
</style>

<script>
    // Copy to clipboard logic
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-clipboard');
            navigator.clipboard.writeText(textToCopy).then(() => {
                const originalIcon = this.innerHTML;
                this.innerHTML = "<i class='bx bx-check' style='color:#10b981;'></i>";
                setTimeout(() => { this.innerHTML = originalIcon; }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        });
    });

    // Toggle password visibility
    function togglePasswordVisibility(id) {
        const input = document.querySelector('.pwd-field-' + id);
        const icon = document.querySelector('.eye-icon-' + id);
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('bx-show');
            icon.classList.add('bx-hide');
            icon.style.color = "#ef4444";
        } else {
            input.type = "password";
            icon.classList.remove('bx-hide');
            icon.classList.add('bx-show');
            icon.style.color = "#b45309";
        }
    }
</script>
@endsection
    </section>
</div>

<script>
    // Copy to clipboard logic
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-clipboard');
            navigator.clipboard.writeText(textToCopy).then(() => {
                const originalIcon = this.innerHTML;
                this.innerHTML = "<i class='bx bx-check' style='color:#10b981;'></i>";
                setTimeout(() => { this.innerHTML = originalIcon; }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        });
    });

    // Toggle password visibility
    function togglePasswordVisibility(id) {
        const input = document.querySelector('.pwd-field-' + id);
        const icon = document.querySelector('.eye-icon-' + id);
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('bx-show');
            icon.classList.add('bx-hide');
            icon.style.color = "#ef4444";
        } else {
            input.type = "password";
            icon.classList.remove('bx-hide');
            icon.classList.add('bx-show');
            icon.style.color = "#6b7280";
        }
    }
</script>
@endsection
