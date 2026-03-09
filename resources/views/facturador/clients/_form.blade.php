{{-- Partial compartido por clients/create y clients/edit --}}
{{-- Variables: $client (si edición), $isEditing (bool) --}}

<div class="form-group">
  <label>Tipo de Documento *</label>
  <select name="codigo_tipo_documento" class="form-input" required {{ $isEditing ? 'disabled' : '' }}>
    <option value="">— Seleccionar —</option>
    <option value="6" {{ old('codigo_tipo_documento', $client->codigo_tipo_documento ?? '') === '6' ? 'selected' : '' }}>6 — RUC</option>
    <option value="1" {{ old('codigo_tipo_documento', $client->codigo_tipo_documento ?? '') === '1' ? 'selected' : '' }}>1 — DNI</option>
    <option value="4" {{ old('codigo_tipo_documento', $client->codigo_tipo_documento ?? '') === '4' ? 'selected' : '' }}>4 — Carnet de Extranjería</option>
    <option value="7" {{ old('codigo_tipo_documento', $client->codigo_tipo_documento ?? '') === '7' ? 'selected' : '' }}>7 — Pasaporte</option>
    <option value="0" {{ old('codigo_tipo_documento', $client->codigo_tipo_documento ?? '') === '0' ? 'selected' : '' }}>0 — Sin documento</option>
  </select>
  @if($isEditing)
    <input type="hidden" name="codigo_tipo_documento" value="{{ $client->codigo_tipo_documento }}">
  @endif
  @error('codigo_tipo_documento')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-group">
  <label>Número de Documento *</label>
  <div style="display:flex; gap:.5rem; align-items:flex-start;">
    <div style="flex:1;">
      <input type="text" name="numero_documento" id="cliente-numero-doc" class="form-input"
        value="{{ old('numero_documento', $client->numero_documento ?? '') }}"
        {{ $isEditing ? 'readonly' : 'required' }}>
    </div>
    @if(!$isEditing)
      <button type="button" id="btn-buscar-doc" class="btn-secondary" style="white-space:nowrap; padding:.5rem .9rem; font-size:.85rem; flex-shrink:0;">
        <i class='bx bx-search'></i> Buscar
      </button>
    @endif
  </div>
  <p id="lookup-status" style="font-size:.8rem; margin-top:.3rem; display:none;"></p>
  @error('numero_documento')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-group full-width">
  <label>Razón Social / Nombre *</label>
  <input type="text" name="nombre_razon_social" id="cliente-nombre" class="form-input"
    value="{{ old('nombre_razon_social', $client->nombre_razon_social ?? '') }}" required>
  @error('nombre_razon_social')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-group full-width">
  <label>Correo Electrónico</label>
  <input type="email" name="correo" class="form-input"
    value="{{ old('correo', $client->correo ?? '') }}">
  @error('correo')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-group full-width">
  <label>Dirección</label>
  <input type="text" name="direccion" id="cliente-direccion" class="form-input"
    value="{{ old('direccion', $client->direccion ?? '') }}">
</div>

<div class="form-group">
  <label>País</label>
  <input type="text" name="codigo_pais" class="form-input" maxlength="2"
    placeholder="PE"
    value="{{ old('codigo_pais', $client->codigo_pais ?? 'PE') }}">
  @error('codigo_pais')<p class="form-error">{{ $message }}</p>@enderror
</div>

@if($isEditing)
<div class="form-group">
  <label>Estado</label>
  <select name="activo" class="form-input">
    <option value="1" {{ old('activo', $client->activo ?? true) ? 'selected' : '' }}>Activo</option>
    <option value="0" {{ !old('activo', $client->activo ?? true) ? 'selected' : '' }}>Inactivo</option>
  </select>
</div>

{{-- ── Credenciales SOL para acceso automático a SUNAT ── --}}
@if(($client->codigo_tipo_documento ?? '') === '6')
<div class="form-group full-width" style="margin-top:.5rem; padding-top:1rem; border-top:1px dashed #e2e8f0;">
  <p style="font-size:.78rem; font-weight:700; color:#1a3a6b; text-transform:uppercase; letter-spacing:.05em; margin:0 0 .75rem;">
    <i class='bx bx-shield-quarter' style="vertical-align:middle;"></i>
    Credenciales SOL para SUNAT
  </p>
  <p style="font-size:.8rem; color:#64748b; margin:0 0 .75rem;">
    Permite abrir el portal de SUNAT con sesión automática desde el botón <strong>SUNAT</strong> del listado de clientes. La clave se guarda cifrada.
  </p>
</div>
<div class="form-group">
  <label>Usuario SOL</label>
  <input type="text"
    name="usuario_sol"
    class="form-input"
    autocomplete="off"
    placeholder="Ej. NOMBRE_USUARIO"
    value="{{ old('usuario_sol', $client->usuario_sol ?? '') }}">
  @error('usuario_sol')<p class="form-error">{{ $message }}</p>@enderror
</div>
<div class="form-group">
  <label>Clave SOL</label>
  <input type="password"
    name="clave_sol"
    class="form-input"
    autocomplete="new-password"
    placeholder="{{ $client->clave_sol ? '(guardada — dejar en blanco para no cambiar)' : 'Ingresar clave SOL' }}">
  @error('clave_sol')<p class="form-error">{{ $message }}</p>@enderror
</div>
@endif
@endif

@if(!$isEditing)
<script>
document.getElementById('btn-buscar-doc')?.addEventListener('click', async function () {
  const tipoDoc = document.querySelector('[name="codigo_tipo_documento"]')?.value;
  const numero  = document.getElementById('cliente-numero-doc')?.value.trim();
  const status  = document.getElementById('lookup-status');

  if (!numero) {
    status.textContent = 'Ingrese el número de documento antes de buscar.';
    status.style.color = '#dc2626'; status.style.display = 'block'; return;
  }
  if (!['1','6'].includes(tipoDoc)) {
    status.textContent = 'Solo se puede autocompletar con RUC (tipo 6) o DNI (tipo 1).';
    status.style.color = '#d97706'; status.style.display = 'block'; return;
  }

  this.disabled = true;
  status.textContent = 'Buscando...';
  status.style.color = '#6b7280'; status.style.display = 'block';

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const res = await fetch(`{{ route('facturador.clients.lookup-doc') }}?type=${encodeURIComponent(tipoDoc)}&number=${encodeURIComponent(numero)}`, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    });
    const data = await res.json();

    if (data.ok) {
      const nombre = document.getElementById('cliente-nombre');
      if (nombre) nombre.value = data.nombre ?? '';
      const dir = document.getElementById('cliente-direccion');
      if (dir && data.direccion) dir.value = data.direccion;
      status.textContent = '✓ Datos cargados correctamente.';
      status.style.color = '#059669';
    } else {
      status.textContent = '✗ ' + (data.error ?? 'No se encontraron datos.');
      status.style.color = '#dc2626';
    }
  } catch (e) {
    status.textContent = '✗ Error de conexión al buscar.';
    status.style.color = '#dc2626';
  } finally {
    this.disabled = false;
  }
});
</script>
@endif
