@php
  $units = [
    'NIU' => 'NIU - Unidad',
    'ZZ'  => 'ZZ - Servicio',
    'KGM' => 'KGM - Kilogramos',
    'MTR' => 'MTR - Metro',
    'LTR' => 'LTR - Litro',
    'BX'  => 'BX - Caja',
    'BG'  => 'BG - Bolsa',
    'BO'  => 'BO - Botella',
    'PA'  => 'PA - Par',
    'PK'  => 'PK - Paquete',
    'DZN' => 'DZN - Docena',
    'SET' => 'SET - Conjunto',
    'MIL' => 'MIL - Millar',
    'GRM' => 'GRM - Gramo',
    'MLT' => 'MLT - Mililitro',
    'M2'  => 'M2 - Metros cuadrados',
    'M3'  => 'M3 - Metros cúbicos',
    'TNE' => 'TNE - Tonelada',
  ];
@endphp
@foreach($units as $code => $label)
  <option value="{{ $code }}" {{ ($selected ?? 'NIU') === $code ? 'selected' : '' }}>{{ $label }}</option>
@endforeach
