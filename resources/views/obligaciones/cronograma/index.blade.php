@extends('layouts.app')

@section('title', 'Cronograma de Obligaciones Mensuales | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .excel-shell {
      display: grid;
      gap: 1rem;
    }
    .excel-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 1rem;
      margin-bottom: .85rem;
    }
    .excel-toolbar h1 {
      margin: 0;
      font-size: 1.35rem;
      color: #0f172a;
    }
    .excel-toolbar p {
      margin: .25rem 0 0;
      color: #64748b;
      font-size: .9rem;
    }
    .excel-source {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      color: #475569;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 999px;
      padding: .42rem .75rem;
      font-size: .78rem;
      font-weight: 700;
      white-space: nowrap;
      text-decoration: none;
    }
    .excel-table-wrap {
      width: 100%;
      overflow: auto;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      background: #fff;
    }
    .excel-table {
      width: 100%;
      min-width: 980px;
      border-collapse: collapse;
      table-layout: fixed;
      font-size: .86rem;
    }
    .excel-table th,
    .excel-table td {
      border: 1px solid #cbd5e1;
      vertical-align: top;
    }
    .excel-table th {
      background: #e8f0fe;
      color: #0f172a;
      font-size: .75rem;
      font-weight: 800;
      text-transform: uppercase;
      text-align: center;
      letter-spacing: .02em;
      padding: .6rem .45rem;
    }
    .excel-table td {
      min-height: 54px;
      padding: .45rem;
      color: #334155;
    }
    .company-cell {
      display: flex;
      flex-direction: column;
      gap: .35rem;
      min-height: 118px;
      max-height: 220px;
      overflow-y: auto;
    }
    .company-chip {
      width: 100%;
      border: 1px solid #dbe4ef;
      background: #fff;
      border-radius: 4px;
      padding: .42rem .45rem;
      text-align: left;
      cursor: pointer;
      transition: background .15s, border-color .15s;
    }
    .company-chip:hover {
      background: #f0f9ff;
      border-color: #38bdf8;
    }
    .company-chip strong {
      display: block;
      color: #0f172a;
      font-size: .78rem;
      line-height: 1.2;
      overflow-wrap: anywhere;
    }
    .company-chip span {
      display: block;
      margin-top: .14rem;
      color: #64748b;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: .72rem;
    }
    .empty-cell {
      color: #94a3b8;
      font-size: .78rem;
      text-align: center;
      padding-top: 1.25rem;
    }
    .schedule-table td {
      text-align: center;
      vertical-align: middle;
      height: 54px;
      padding: 0;
    }
    .period-cell {
      background: #f8fafc;
      color: #0f172a;
      font-weight: 800;
      text-align: left !important;
      padding: .55rem .65rem !important;
      width: 92px;
    }
    .due-cell-btn {
      width: 100%;
      height: 54px;
      border: 0;
      background: #f8fafc;
      color: #0f172a;
      font-weight: 800;
      cursor: pointer;
      transition: transform .08s, box-shadow .12s, background .12s;
    }
    .due-cell-btn:hover {
      transform: translateY(-1px);
      box-shadow: inset 0 0 0 2px #38bdf8;
    }
    .due-cell-btn.tone-future { background: #f1f5f9; color: #475569; }
    .due-cell-btn.tone-soon { background: #fef3c7; color: #92400e; }
    .due-cell-btn.tone-overdue { background: #fee2e2; color: #991b1b; }
    .legend-row {
      display: flex;
      gap: .6rem;
      flex-wrap: wrap;
      color: #64748b;
      font-size: .78rem;
      margin-top: .7rem;
    }
    .legend-pill {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
    }
    .legend-dot {
      width: .7rem;
      height: .7rem;
      border-radius: 999px;
      display: inline-block;
    }
    .dot-green { background: #22c55e; }
    .dot-red { background: #ef4444; }
    .dot-yellow { background: #f59e0b; }
    .dot-gray { background: #94a3b8; }
    .modal-backdrop-cron {
      position: fixed;
      inset: 0;
      z-index: 80;
      background: rgba(15, 23, 42, .55);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .modal-backdrop-cron.is-open {
      display: flex;
    }
    .cron-modal {
      width: min(680px, 100%);
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 24px 80px rgba(15, 23, 42, .28);
      overflow: hidden;
    }
    .cron-modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      padding: 1rem 1.2rem;
      border-bottom: 1px solid #e2e8f0;
    }
    .cron-modal-header h2 {
      margin: 0;
      color: #0f172a;
      font-size: 1rem;
    }
    .cron-modal-close {
      border: 0;
      background: #f1f5f9;
      border-radius: 6px;
      width: 2rem;
      height: 2rem;
      cursor: pointer;
    }
    .cron-modal-body {
      padding: 1.2rem;
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .85rem;
    }
    .cron-field {
      display: flex;
      flex-direction: column;
      gap: .32rem;
    }
    .cron-field.full {
      grid-column: 1 / -1;
    }
    .cron-field label {
      font-size: .74rem;
      text-transform: uppercase;
      font-weight: 800;
      color: #64748b;
    }
    .cron-input {
      min-height: 2.45rem;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      padding: .55rem .7rem;
      font-size: .9rem;
      color: #0f172a;
      background: #fff;
    }
    textarea.cron-input {
      min-height: 78px;
      resize: vertical;
    }
    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      border-radius: 999px;
      padding: .45rem .75rem;
      font-weight: 800;
      font-size: .82rem;
      width: fit-content;
    }
    .status-pending { background: #f1f5f9; color: #475569; }
    .status-on-time { background: #dcfce7; color: #166534; }
    .status-late,
    .status-overdue { background: #fee2e2; color: #991b1b; }
    .cron-modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: .65rem;
      padding: 1rem 1.2rem;
      border-top: 1px solid #e2e8f0;
      background: #f8fafc;
    }
    .cron-btn {
      border: 1px solid transparent;
      border-radius: 6px;
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      min-height: 2.35rem;
      padding: .45rem .85rem;
      font-weight: 800;
      cursor: pointer;
    }
    .cron-btn-primary {
      background: #0f766e;
      color: #fff;
    }
    .cron-btn-secondary {
      background: #fff;
      border-color: #cbd5e1;
      color: #475569;
    }
    @media (max-width: 760px) {
      .excel-toolbar {
        flex-direction: column;
      }
      .cron-modal-body {
        grid-template-columns: 1fr;
      }
    }
    body.dark-mode .excel-toolbar h1,
    body.dark-mode .company-chip strong,
    body.dark-mode .period-cell,
    body.dark-mode .cron-modal-header h2 {
      color: #f8fafc;
    }
    body.dark-mode .excel-table-wrap,
    body.dark-mode .cron-modal,
    body.dark-mode .company-chip,
    body.dark-mode .cron-input {
      background: #0f172a;
      border-color: #334155;
      color: #f8fafc;
    }
    body.dark-mode .excel-table th { background: #1e293b; color: #cbd5e1; }
    body.dark-mode .excel-table td { border-color: #334155; }
    body.dark-mode .period-cell,
    body.dark-mode .due-cell-btn.tone-future,
    body.dark-mode .cron-modal-footer,
    body.dark-mode .cron-modal-close {
      background: #1e293b;
      color: #cbd5e1;
    }
  </style>
@endpush

@section('content')
  @php
    $statusLabels = [
      'pending' => 'Pendiente',
      'presented_on_time' => 'Presentado dentro de plazo',
      'presented_late' => 'Presentado fuera de plazo',
      'overdue' => 'Vencido',
    ];

    $declarationPayload = $declarations->mapWithKeys(function ($declaration) use ($statusLabels) {
      return [
        $declaration->company_id.'-'.$declaration->period_month => [
          'id' => $declaration->id,
          'company_id' => $declaration->company_id,
          'period_month' => $declaration->period_month,
          'due_group' => $declaration->due_group,
          'due_date' => optional($declaration->due_date)->format('Y-m-d'),
          'presentation_date' => optional($declaration->presentation_date)->format('Y-m-d'),
          'status' => $declaration->status,
          'status_label' => $statusLabels[$declaration->status] ?? 'Pendiente',
          'observation' => $declaration->observation,
        ],
      ];
    })->values();
  @endphp

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
          @if(session('status'))
            <div class="placeholder-content module-alert module-flash" data-flash-message>
              <p>{{ session('status') }}</p>
              <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close>
                <i class='bx bx-x'></i>
              </button>
            </div>
          @endif

          @if($errors->any())
            <div class="placeholder-content module-alert">
              @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
              @endforeach
            </div>
          @endif

          <div class="placeholder-content module-card-wide">
            <div class="excel-toolbar">
              <div>
                <h1>Cronograma de Obligaciones Mensuales SUNAT {{ $scheduleYear }}</h1>
                <p>Vista tipo Excel con empresas agrupadas por ultimo digito de RUC y vencimientos mensuales.</p>
              </div>
              <a class="excel-source" href="https://www.sunat.gob.pe/orientacion/cronogramas/2026/cObligacionMensual2026.html" target="_blank" rel="noopener noreferrer">
                <i class='bx bx-link-external'></i> Fuente SUNAT
              </a>
            </div>

            <div class="excel-shell">
              <section>
                <div class="excel-table-wrap">
                  <table class="excel-table">
                    <thead>
                      <tr>
                        @foreach($groups as $groupKey => $group)
                          <th>{{ $group['label'] }}</th>
                        @endforeach
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        @foreach($groups as $groupKey => $group)
                          <td>
                            <div class="company-cell">
                              @forelse($groupedCompanies[$groupKey] as $company)
                                <button type="button"
                                        class="company-chip"
                                        data-open-declaration
                                        data-company-id="{{ $company->id }}"
                                        data-group="{{ $groupKey }}">
                                  <strong>{{ $company->name }}</strong>
                                  <span>{{ $company->ruc }}</span>
                                </button>
                              @empty
                                <div class="empty-cell">Sin empresas</div>
                              @endforelse
                            </div>
                          </td>
                        @endforeach
                      </tr>
                    </tbody>
                  </table>
                </div>
              </section>

              <section>
                <div class="excel-table-wrap">
                  <table class="excel-table schedule-table">
                    <thead>
                      <tr>
                        <th style="width:92px;">Periodo</th>
                        @foreach($groups as $group)
                          <th>{{ $group['label'] }}</th>
                        @endforeach
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($scheduleRows as $row)
                        <tr>
                          <td class="period-cell">{{ $row['period'] }}</td>
                          @foreach($groups as $groupKey => $group)
                            @php $cell = $row['cells'][$groupKey]; @endphp
                            <td>
                              <button type="button"
                                      class="due-cell-btn tone-{{ $cell['tone'] }}"
                                      data-open-declaration
                                      data-period-month="{{ $row['month'] }}"
                                      data-group="{{ $groupKey }}"
                                      data-due-date="{{ $cell['date'] }}">
                                {{ $cell['label'] }}
                              </button>
                            </td>
                          @endforeach
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                <div class="legend-row">
                  <span class="legend-pill"><span class="legend-dot dot-green"></span>Presentado dentro de plazo</span>
                  <span class="legend-pill"><span class="legend-dot dot-red"></span>Vencido o fuera de plazo</span>
                  <span class="legend-pill"><span class="legend-dot dot-yellow"></span>Proximo a vencer</span>
                  <span class="legend-pill"><span class="legend-dot dot-gray"></span>Periodo futuro</span>
                </div>
              </section>
            </div>
          </div>
        </div>
      </main>
    </section>
  </div>

  <div class="modal-backdrop-cron" data-cron-modal>
    <form method="POST" action="{{ route('obligaciones.cronograma.store') }}" class="cron-modal" data-cron-form>
      @csrf
      <div class="cron-modal-header">
        <h2>Registrar declaracion mensual</h2>
        <button type="button" class="cron-modal-close" data-cron-close aria-label="Cerrar">
          <i class='bx bx-x'></i>
        </button>
      </div>

      <div class="cron-modal-body">
        <div class="cron-field full">
          <label>Empresa</label>
          <select name="company_id" class="cron-input" data-company-select required></select>
        </div>

        <div class="cron-field">
          <label>RUC</label>
          <input type="text" class="cron-input" data-ruc-output readonly>
        </div>

        <div class="cron-field">
          <label>Periodo</label>
          <select name="period_month" class="cron-input" data-period-select required>
            @foreach($monthLabels as $monthNumber => $label)
              <option value="{{ $monthNumber }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div class="cron-field">
          <label>Grupo de vencimiento</label>
          <select name="due_group" class="cron-input" data-group-select required>
            @foreach($groups as $groupKey => $group)
              <option value="{{ $groupKey }}">{{ $group['label'] }}</option>
            @endforeach
          </select>
        </div>

        <div class="cron-field">
          <label>Fecha de vencimiento</label>
          <input type="text" class="cron-input" data-due-date-output readonly>
        </div>

        <div class="cron-field">
          <label>Fecha real de presentacion</label>
          <input type="date" name="presentation_date" class="cron-input" data-presentation-date>
        </div>

        <div class="cron-field">
          <label>Estado</label>
          <span class="status-pill status-pending" data-status-output>Pendiente</span>
        </div>

        <div class="cron-field full">
          <label>Observacion opcional</label>
          <textarea name="observation" class="cron-input" data-observation placeholder="Detalle interno para el equipo contable"></textarea>
        </div>
      </div>

      <div class="cron-modal-footer">
        <button type="button" class="cron-btn cron-btn-secondary" data-cron-close>Cancelar</button>
        <button type="submit" class="cron-btn cron-btn-primary">
          <i class='bx bx-save'></i> Guardar registro
        </button>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
<script>
  const companies = @json($companyOptions);
  const schedule = @json($scheduleRows);
  const declarations = @json($declarationPayload);
  const groups = @json($groups);
  const today = '{{ $today }}';
  const currentPeriodMonth = {{ min(12, max(1, now()->month)) }};

  const modal = document.querySelector('[data-cron-modal]');
  const form = document.querySelector('[data-cron-form]');
  const companySelect = document.querySelector('[data-company-select]');
  const periodSelect = document.querySelector('[data-period-select]');
  const groupSelect = document.querySelector('[data-group-select]');
  const rucOutput = document.querySelector('[data-ruc-output]');
  const dueDateOutput = document.querySelector('[data-due-date-output]');
  const presentationDate = document.querySelector('[data-presentation-date]');
  const statusOutput = document.querySelector('[data-status-output]');
  const observation = document.querySelector('[data-observation]');

  const declarationMap = new Map(declarations.map((item) => [`${item.company_id}-${item.period_month}`, item]));
  let fixedGroup = null;

  const statusLabels = {
    pending: 'Pendiente',
    presented_on_time: 'Presentado dentro de plazo',
    presented_late: 'Presentado fuera de plazo',
    overdue: 'Vencido',
  };

  const statusClasses = {
    pending: 'status-pending',
    presented_on_time: 'status-on-time',
    presented_late: 'status-late',
    overdue: 'status-overdue',
  };

  const formatDate = (isoDate) => {
    if (!isoDate) return '';
    const [year, month, day] = isoDate.split('-');
    return `${day}/${month}/${year}`;
  };

  const getDueDate = (month, groupKey) => {
    const row = schedule.find((item) => String(item.month) === String(month));
    return row?.cells?.[groupKey]?.date || '';
  };

  const resolveStatus = () => {
    const due = getDueDate(periodSelect.value, groupSelect.value);
    const submitted = presentationDate.value;

    if (submitted) {
      return submitted <= due ? 'presented_on_time' : 'presented_late';
    }

    return today > due ? 'overdue' : 'pending';
  };

  const setStatus = (status) => {
    statusOutput.className = `status-pill ${statusClasses[status] || 'status-pending'}`;
    statusOutput.textContent = statusLabels[status] || statusLabels.pending;
  };

  const selectedCompany = () => companies.find((company) => String(company.id) === String(companySelect.value));

  const fillCompanies = (groupKey, selectedId = '') => {
    const filtered = groupKey
      ? companies.filter((company) => company.groups.includes(groupKey))
      : companies;

    companySelect.innerHTML = filtered.map((company) => (
      `<option value="${company.id}">${company.name} - ${company.ruc}</option>`
    )).join('');

    if (selectedId && filtered.some((company) => String(company.id) === String(selectedId))) {
      companySelect.value = selectedId;
    }
  };

  const applyDeclaration = () => {
    const company = selectedCompany();
    if (!company) return;

    const declaration = declarationMap.get(`${company.id}-${periodSelect.value}`);
    if (declaration) {
      groupSelect.value = declaration.due_group || groupSelect.value;
      presentationDate.value = declaration.presentation_date || '';
      observation.value = declaration.observation || '';
    } else {
      presentationDate.value = '';
      observation.value = '';
      if (!fixedGroup) {
        groupSelect.value = company.groups[0];
      }
    }
  };

  const refreshModal = () => {
    const company = selectedCompany();
    rucOutput.value = company?.ruc || '';

    if (fixedGroup) {
      groupSelect.value = fixedGroup;
    }

    dueDateOutput.value = formatDate(getDueDate(periodSelect.value, groupSelect.value));
    setStatus(resolveStatus());
  };

  const openModal = ({ companyId = '', periodMonth = currentPeriodMonth, groupKey = null } = {}) => {
    fixedGroup = groupKey;
    fillCompanies(groupKey, companyId);
    periodSelect.value = periodMonth;
    groupSelect.disabled = Boolean(groupKey);

    if (groupKey) {
      groupSelect.value = groupKey;
    }

    applyDeclaration();
    refreshModal();
    modal.classList.add('is-open');
  };

  document.querySelectorAll('[data-open-declaration]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
      openModal({
        companyId: trigger.dataset.companyId || '',
        periodMonth: trigger.dataset.periodMonth || currentPeriodMonth,
        groupKey: trigger.dataset.group || null,
      });
    });
  });

  document.querySelectorAll('[data-cron-close]').forEach((button) => {
    button.addEventListener('click', () => modal.classList.remove('is-open'));
  });

  modal.addEventListener('click', (event) => {
    if (event.target === modal) {
      modal.classList.remove('is-open');
    }
  });

  companySelect.addEventListener('change', () => {
    applyDeclaration();
    refreshModal();
  });
  periodSelect.addEventListener('change', () => {
    applyDeclaration();
    refreshModal();
  });
  groupSelect.addEventListener('change', refreshModal);
  presentationDate.addEventListener('change', refreshModal);

  form.addEventListener('submit', () => {
    groupSelect.disabled = false;
  });

  document.querySelectorAll('[data-flash-message]').forEach((flash) => {
    const closeBtn = flash.querySelector('[data-flash-close]');
    if (closeBtn) closeBtn.addEventListener('click', () => flash.remove());
    window.setTimeout(() => { if (document.body.contains(flash)) flash.remove(); }, 4500);
  });
</script>
@endpush
