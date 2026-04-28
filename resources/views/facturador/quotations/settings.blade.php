@extends('layouts.app')

@section('title', 'Configuración del Cotizador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .settings-grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:1.25rem;align-items:start}
    .settings-section{border:1px solid #e5e7eb;border-radius:12px;padding:1rem;margin-bottom:1rem;background:#fff}
    .settings-section h2{font-size:1rem;margin:0 0 .85rem;color:#111827}
    .settings-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem}
    .settings-field{display:flex;flex-direction:column;gap:.35rem;margin-bottom:.8rem}
    .settings-field label{font-size:.82rem;font-weight:700;color:#374151}
    .settings-field input,.settings-field textarea,.settings-field select{width:100%;border:1px solid #dbe3ea;border-radius:8px;padding:.58rem .72rem;font:inherit;box-sizing:border-box}
    .settings-field textarea{resize:vertical;min-height:82px}
    .toggle-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;border:1px solid #dbe3ea;border-radius:10px;padding:.8rem 1rem;background:#f8fafc}
    .toggle-row strong{display:block;font-size:.9rem;color:#111827}
    .toggle-row span{display:block;font-size:.78rem;color:#64748b;margin-top:.15rem}
    .logo-preview{height:92px;border:1px dashed #cbd5e1;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#f8fafc;overflow:hidden}
    .logo-preview img{max-width:100%;max-height:82px;object-fit:contain}
    .quote-preview-card{position:sticky;top:1rem;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff}
    .quote-preview-head{padding:1rem;color:#fff;background:var(--quote-primary,#013b33);display:flex;align-items:center;justify-content:space-between;gap:.8rem}
    .quote-preview-logo{width:86px;height:46px;border-radius:8px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;overflow:hidden}
    .quote-preview-logo img{max-width:80px;max-height:40px;object-fit:contain}
    .quote-preview-body{padding:1rem;background:var(--quote-secondary,#eef7f5)}
    .quote-preview-box{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:.85rem;margin-bottom:.8rem}
    .bank-row{display:grid;grid-template-columns:96px 1fr 120px 1fr auto;gap:.55rem;align-items:end;border:1px solid #e5e7eb;border-radius:10px;padding:.75rem;margin-bottom:.6rem;background:#fbfdff}
    .bank-row .settings-field{margin:0}
    .bank-icon-box{height:80px;border:1px dashed #cbd5e1;border-radius:10px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;color:#94a3b8;font-size:.75rem;text-align:center}
    .bank-icon-box img{max-width:88px;max-height:72px;object-fit:contain}
    .bank-icon-actions{display:flex;flex-direction:column;gap:.35rem}
    .bank-icon-actions input[type=file]{font-size:.72rem;padding:.35rem}
    .btn-light{display:inline-flex;align-items:center;gap:.35rem;padding:.55rem .8rem;border-radius:8px;border:1px solid #dbe3ea;background:#fff;color:#0f172a;text-decoration:none;font-weight:700;cursor:pointer}
    .btn-danger-soft{border:none;border-radius:8px;background:#fee2e2;color:#b91c1c;padding:.55rem .65rem;cursor:pointer}
    @media(max-width:1050px){.settings-grid{grid-template-columns:1fr}.quote-preview-card{position:static}.settings-row,.bank-row{grid-template-columns:1fr}}
  </style>
@endpush

@section('content')
<div class="app-layout">
  <aside class="sidebar-premium">
    <div class="sidebar-header">
      <img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta" class="header-logo">
      <div class="header-text"><h2>Portal Mendieta</h2><p>Panel interno</p></div>
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
        @if(session('success'))
          <div class="placeholder-content module-alert" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">
            {{ session('success') }}
          </div>
        @endif

        @if($errors->any())
          <div class="placeholder-content module-alert">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
          </div>
        @endif

        @php
          $accounts = old('bank_accounts', $settings->quote_payment_info ?? $settings->bank_accounts ?? []);
          if (empty($accounts)) {
            $accounts = [['banco' => '', 'titular' => '', 'moneda' => 'PEN', 'cuenta' => '', 'cci' => '']];
          }
        @endphp

        <form method="POST" action="{{ route('facturador.quote-settings.update') }}" enctype="multipart/form-data" class="placeholder-content module-card-wide">
          @csrf

          <div class="module-toolbar" style="align-items:flex-start;gap:1rem;">
            <div>
              <h1>Configuración del Cotizador</h1>
              <p style="margin:.35rem 0 0;color:#64748b;">Branding, colores y datos de pago para {{ $company->name }}.</p>
            </div>
            <a class="btn-light" href="{{ route('facturador.quotations.create') }}"><i class='bx bx-receipt'></i> Abrir cotizador</a>
          </div>

          <div class="settings-grid">
            <div>
              <div class="settings-section">
                <div class="toggle-row">
                  <div>
                    <strong>Cotizador habilitado</strong>
                    <span>Si se desactiva, se oculta para usuarios de la empresa. Admin siempre conserva acceso.</span>
                  </div>
                  <input type="checkbox" name="quote_enabled" value="1" @checked(old('quote_enabled', $settings->quote_enabled ?? true))>
                </div>
              </div>

              <div class="settings-section">
                <h2>Imagen y Colores</h2>
                <div class="settings-row">
                  <div class="settings-field">
                    <label>Logo JPG/PNG</label>
                    <input type="file" name="quote_logo_file" id="quoteLogoInput" accept="image/png,image/jpeg">
                    <small style="color:#64748b;">Se guarda en base64 en la BD para que el PDF lo renderice sin depender de storage público.</small>
                  </div>
                  <div>
                    <label style="font-size:.82rem;font-weight:700;color:#374151;display:block;margin-bottom:.35rem;">Vista del logo</label>
                    <div class="logo-preview" id="logoPreview">
                      @if($settings->quote_logo_src)
                        <img src="{{ $settings->quote_logo_src }}" alt="Logo cotizador">
                      @else
                        <span style="color:#94a3b8;font-size:.85rem;">Sin logo personalizado</span>
                      @endif
                    </div>
                    @if($settings->quote_logo_src)
                      <label style="display:flex;align-items:center;gap:.4rem;margin-top:.45rem;font-size:.82rem;color:#475569;">
                        <input type="checkbox" name="remove_logo" value="1"> Quitar logo guardado
                      </label>
                    @endif
                  </div>
                </div>
                <div class="settings-row">
                  <div class="settings-field">
                    <label>Color principal</label>
                    <input type="color" name="primary_color" id="primaryColor" value="{{ old('primary_color', $settings->primary_color ?? '#013b33') }}">
                  </div>
                  <div class="settings-field">
                    <label>Color secundario</label>
                    <input type="color" name="secondary_color" id="secondaryColor" value="{{ old('secondary_color', $settings->secondary_color ?? '#eef7f5') }}">
                  </div>
                </div>
              </div>

              <div class="settings-section">
                <h2>Datos del Emisor</h2>
                <div class="settings-row">
                  <div class="settings-field"><label>Razón social</label><input name="company_name" value="{{ old('company_name', $settings->company_name ?? $company->name) }}"></div>
                  <div class="settings-field"><label>RUC</label><input name="ruc" value="{{ old('ruc', $settings->ruc ?? $company->ruc) }}"></div>
                  <div class="settings-field"><label>Teléfono</label><input name="phone" value="{{ old('phone', $settings->phone) }}"></div>
                  <div class="settings-field"><label>Correo</label><input name="email" type="email" value="{{ old('email', $settings->email) }}"></div>
                </div>
                <div class="settings-field"><label>Dirección</label><input name="address" value="{{ old('address', $settings->address) }}"></div>
                <div class="settings-field"><label>Web</label><input name="website" value="{{ old('website', $settings->website) }}"></div>
              </div>

              <div class="settings-section">
                <h2>Datos de Pago</h2>
                <div id="bankRows">
                  @foreach($accounts as $i => $account)
                    <div class="bank-row">
                      <div class="bank-icon-actions">
                        <div class="bank-icon-box">
                          @if(!empty($account['icon_base64']))
                            <img src="{{ $account['icon_base64'] }}" alt="Icono banco">
                          @else
                            Icono/QR
                          @endif
                        </div>
                        <input type="hidden" name="bank_accounts[{{ $i }}][icon_base64]" value="{{ $account['icon_base64'] ?? '' }}">
                        <input type="file" name="bank_account_icons[{{ $i }}]" accept="image/png,image/jpeg" class="bankIconInput">
                      </div>
                      <div class="settings-field"><label>Banco</label><input name="bank_accounts[{{ $i }}][banco]" value="{{ $account['banco'] ?? '' }}"></div>
                      <div class="settings-field"><label>Moneda</label><select name="bank_accounts[{{ $i }}][moneda]"><option value="PEN" @selected(($account['moneda'] ?? 'PEN') === 'PEN')>PEN</option><option value="USD" @selected(($account['moneda'] ?? '') === 'USD')>USD</option></select></div>
                      <div class="settings-field"><label>Cuenta</label><input name="bank_accounts[{{ $i }}][cuenta]" value="{{ $account['cuenta'] ?? '' }}"></div>
                      <button type="button" class="btn-danger-soft btnRemoveBank" title="Quitar"><i class='bx bx-trash'></i></button>
                      <div class="settings-field"><label>Titular</label><input name="bank_accounts[{{ $i }}][titular]" value="{{ $account['titular'] ?? '' }}"></div>
                      <div class="settings-field" style="grid-column:span 3;"><label>CCI</label><input name="bank_accounts[{{ $i }}][cci]" value="{{ $account['cci'] ?? '' }}"></div>
                    </div>
                  @endforeach
                </div>
                <button type="button" class="btn-light" id="btnAddBank"><i class='bx bx-plus'></i> Agregar cuenta</button>
              </div>

              <div class="settings-section">
                <h2>Textos del Documento</h2>
                <div class="settings-row">
                  <label class="toggle-row"><div><strong>Mostrar IGV</strong><span>Respeta también el check del formulario.</span></div><input type="checkbox" name="show_igv_breakdown" value="1" @checked(old('show_igv_breakdown', $settings->show_igv_breakdown ?? true))></label>
                  <label class="toggle-row"><div><strong>Mostrar pagos</strong><span>Oculta o muestra las cuentas en el preview.</span></div><input type="checkbox" name="show_bank_accounts" value="1" @checked(old('show_bank_accounts', $settings->show_bank_accounts ?? true))></label>
                </div>
                <div class="settings-field" style="margin-top:.8rem;"><label>Términos</label><textarea name="quote_terms">{{ old('quote_terms', $settings->quote_terms) }}</textarea></div>
                <div class="settings-field"><label>Mensaje final</label><textarea name="quote_thanks_message">{{ old('quote_thanks_message', $settings->quote_thanks_message) }}</textarea></div>
                <div class="settings-field"><label>Pie de página</label><textarea name="quote_footer">{{ old('quote_footer', $settings->quote_footer) }}</textarea></div>
              </div>
            </div>

            <aside class="quote-preview-card" id="quotePreviewCard" style="--quote-primary:{{ old('primary_color', $settings->primary_color ?? '#013b33') }};--quote-secondary:{{ old('secondary_color', $settings->secondary_color ?? '#eef7f5') }};">
              <div class="quote-preview-head">
                <div class="quote-preview-logo" id="miniLogoPreview">
                  @if($settings->quote_logo_src)<img src="{{ $settings->quote_logo_src }}" alt="Logo">@else<span style="font-size:.72rem;">LOGO</span>@endif
                </div>
                <div style="text-align:right;font-weight:800;">COTIZACIÓN<br><span style="font-size:.8rem;font-weight:600;">COT-2026-001</span></div>
              </div>
              <div class="quote-preview-body">
                <div class="quote-preview-box">
                  <strong>{{ old('company_name', $settings->company_name ?? $company->name) }}</strong><br>
                  <span style="font-size:.82rem;color:#64748b;">RUC {{ old('ruc', $settings->ruc ?? $company->ruc) }}</span>
                </div>
                <div class="quote-preview-box">
                  <span style="font-size:.75rem;color:#64748b;">Total referencial</span>
                  <div style="font-size:1.5rem;font-weight:900;color:var(--quote-primary);">S/ 1,180.00</div>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;"><i class='bx bx-save'></i> Guardar configuración</button>
              </div>
            </aside>
          </div>
        </form>
      </div>
    </main>
  </section>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const card = document.getElementById('quotePreviewCard');
  document.getElementById('primaryColor')?.addEventListener('input', e => card.style.setProperty('--quote-primary', e.target.value));
  document.getElementById('secondaryColor')?.addEventListener('input', e => card.style.setProperty('--quote-secondary', e.target.value));

  document.getElementById('quoteLogoInput')?.addEventListener('change', function(){
    const file = this.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      const html = `<img src="${e.target.result}" alt="Logo">`;
      document.getElementById('logoPreview').innerHTML = html;
      document.getElementById('miniLogoPreview').innerHTML = html;
    };
    reader.readAsDataURL(file);
  });

  function reindexBanks(){
    document.querySelectorAll('#bankRows .bank-row').forEach((row, i) => {
      row.querySelectorAll('[name]').forEach(input => {
        input.name = input.name
          .replace(/bank_accounts\[\d+\]/, `bank_accounts[${i}]`)
          .replace(/bank_account_icons\[\d+\]/, `bank_account_icons[${i}]`);
      });
    });
  }

  document.getElementById('btnAddBank')?.addEventListener('click', () => {
    const wrap = document.getElementById('bankRows');
    const idx = wrap.querySelectorAll('.bank-row').length;
    const row = document.createElement('div');
    row.className = 'bank-row';
      row.innerHTML = `
      <div class="bank-icon-actions">
        <div class="bank-icon-box">Icono/QR</div>
        <input type="hidden" name="bank_accounts[${idx}][icon_base64]" value="">
        <input type="file" name="bank_account_icons[${idx}]" accept="image/png,image/jpeg" class="bankIconInput">
      </div>
      <div class="settings-field"><label>Banco</label><input name="bank_accounts[${idx}][banco]"></div>
      <div class="settings-field"><label>Moneda</label><select name="bank_accounts[${idx}][moneda]"><option value="PEN">PEN</option><option value="USD">USD</option></select></div>
      <div class="settings-field"><label>Cuenta</label><input name="bank_accounts[${idx}][cuenta]"></div>
      <button type="button" class="btn-danger-soft btnRemoveBank" title="Quitar"><i class='bx bx-trash'></i></button>
      <div class="settings-field"><label>Titular</label><input name="bank_accounts[${idx}][titular]"></div>
      <div class="settings-field" style="grid-column:span 3;"><label>CCI</label><input name="bank_accounts[${idx}][cci]"></div>
    `;
    wrap.appendChild(row);
  });

  document.getElementById('bankRows')?.addEventListener('click', e => {
    const btn = e.target.closest('.btnRemoveBank');
    if (!btn) return;
    if (document.querySelectorAll('#bankRows .bank-row').length <= 1) return;
    btn.closest('.bank-row').remove();
    reindexBanks();
  });

  document.getElementById('bankRows')?.addEventListener('change', e => {
    const input = e.target.closest('.bankIconInput');
    if (!input || !input.files?.[0]) return;
    const row = input.closest('.bank-row');
    const reader = new FileReader();
    reader.onload = event => {
      row.querySelector('.bank-icon-box').innerHTML = `<img src="${event.target.result}" alt="Icono banco">`;
    };
    reader.readAsDataURL(input.files[0]);
  });
})();
</script>
@endpush
