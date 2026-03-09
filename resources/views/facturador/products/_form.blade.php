{{-- Partial compartido por create.blade.php y edit.blade.php --}}
{{-- Variables esperadas: $product (si edición), $isEditing (bool) --}}

<div class="form-group">
  <label>Código Interno *</label>
  <input type="text" name="codigo_interno" class="form-input"
    value="{{ old('codigo_interno', $product->codigo_interno ?? '') }}"
    {{ $isEditing ? 'readonly' : 'required' }}>
  @error('codigo_interno')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-group">
  <label>Tipo *</label>
  <select name="tipo" class="form-input" required>
    <option value="P" {{ old('tipo', $product->tipo ?? 'P') === 'P' ? 'selected' : '' }}>Producto</option>
    <option value="S" {{ old('tipo', $product->tipo ?? '') === 'S' ? 'selected' : '' }}>Servicio</option>
  </select>
  @error('tipo')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-group full-width">
  <label>Descripción *</label>
  <input type="text" name="descripcion" class="form-input"
    value="{{ old('descripcion', $product->descripcion ?? '') }}" required>
  @error('descripcion')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-group">
  <label>Unidad de Medida *</label>
  <select name="codigo_unidad_medida" class="form-input" required>
    @foreach(['NIU'=>'NIU (Unidad)', 'ZZ'=>'ZZ (Servicio)', 'KGM'=>'KGM (Kilogramo)', 'MTR'=>'MTR (Metro)', 'LTR'=>'LTR (Litro)', 'GLL'=>'GLL (Galón)'] as $val => $lbl)
      <option value="{{ $val }}" {{ old('codigo_unidad_medida', $product->codigo_unidad_medida ?? 'NIU') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
    @endforeach
  </select>
  @error('codigo_unidad_medida')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-group">
  <label>Indicador Afecto *</label>
  <select name="codigo_indicador_afecto" class="form-input" required>
    <option value="10" {{ old('codigo_indicador_afecto', $product->codigo_indicador_afecto ?? '10') === '10' ? 'selected' : '' }}>10 — Gravado IGV</option>
    <option value="20" {{ old('codigo_indicador_afecto', $product->codigo_indicador_afecto ?? '') === '20' ? 'selected' : '' }}>20 — Exonerado</option>
    <option value="30" {{ old('codigo_indicador_afecto', $product->codigo_indicador_afecto ?? '') === '30' ? 'selected' : '' }}>30 — Inafecto</option>
    <option value="40" {{ old('codigo_indicador_afecto', $product->codigo_indicador_afecto ?? '') === '40' ? 'selected' : '' }}>40 — Exportación</option>
  </select>
  @error('codigo_indicador_afecto')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-group">
  <label>Valor Unitario (sin IGV) *</label>
  <input type="number" name="valor_unitario" class="form-input"
    step="0.0001" min="0"
    value="{{ old('valor_unitario', isset($product) ? (float)$product->valor_unitario : '') }}" required>
  @error('valor_unitario')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-group">
  <label>Precio Unitario (con IGV) *</label>
  <input type="number" name="precio_unitario" class="form-input"
    step="0.0001" min="0"
    value="{{ old('precio_unitario', isset($product) ? (float)$product->precio_unitario : '') }}" required>
  @error('precio_unitario')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-group">
  <label>Código SUNAT (opcional)</label>
  <input type="text" name="codigo_sunat" class="form-input"
    value="{{ old('codigo_sunat', $product->codigo_sunat ?? '') }}">
</div>

@if($isEditing)
<div class="form-group">
  <label>Estado</label>
  <select name="activo" class="form-input">
    <option value="1" {{ old('activo', $product->activo ?? true) ? 'selected' : '' }}>Activo</option>
    <option value="0" {{ !old('activo', $product->activo ?? true) ? 'selected' : '' }}>Inactivo</option>
  </select>
</div>
@endif
