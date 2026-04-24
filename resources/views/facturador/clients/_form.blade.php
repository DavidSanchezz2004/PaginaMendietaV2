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

<div class="form-group full-width">
  <label style="display:flex; align-items:center; gap:.6rem; cursor:pointer; font-weight:600;">
    <input type="hidden" name="is_retainer_agent" value="0">
    <input type="checkbox" name="is_retainer_agent" value="1"
      {{ old('is_retainer_agent', $client->is_retainer_agent ?? false) ? 'checked' : '' }}
      style="width:1rem; height:1rem; accent-color:#dc2626; cursor:pointer;">
    <span>Es Agente de Retenci&oacute;n</span>
  </label>
  <p style="font-size:.78rem; color:#6b7280; margin:.2rem 0 0 1.6rem;">
    Al marcar esta opci&oacute;n, las facturas emitidas a este cliente aplicar&aacute;n retenci&oacute;n del 3% autom&aacute;ticamente cuando el total supere S/ 700.
  </p>
</div>

@if($isEditing)
<div class="form-group">
  <label>Estado</label>
  <select name="activo" class="form-input">
    <option value="1" {{ old('activo', $client->activo ?? true) ? 'selected' : '' }}>Activo</option>
    <option value="0" {{ !old('activo', $client->activo ?? true) ? 'selected' : '' }}>Inactivo</option>
  </select>
</div>

{{-- Sección de Credenciales SOL eliminada --}}
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
