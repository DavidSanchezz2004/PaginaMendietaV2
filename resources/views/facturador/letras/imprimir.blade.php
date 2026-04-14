<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <title>Letra de Cambio {{ $letra->numero_letra }}</title>
    <style>
      * { box-sizing: border-box; }
      body {
        background: #fff;
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
      }

      /* ── Estilos de impresión ────────────────── */
      @media print {
        body { padding: 0; }
        .no-print { display: none !important; }
        @page { size: A4 landscape; margin: 8mm; }
      }
      @media screen {
        body { background: #e5e5e5; padding: 20px; }
      }

      .outer-wrapper {
        width: 1050px;
        height: 480px;
        margin: 0 auto;
        background: #fff;
        border: 3px solid #000;
        padding: 2px;
      }
      .inner-container {
        border: 2px solid #000;
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
      }

      /* SIDEBAR CLAUSULAS */
      .sidebar-clauses {
        position: absolute; left: 0; top: 0; bottom: 20px; width: 80px;
        display: flex; justify-content: center; align-items: center; padding: 10px;
      }
      .sidebar-clauses .text {
        writing-mode: vertical-rl; transform: rotate(180deg);
        font-size: 8px; line-height: 1.1; text-align: justify;
        height: 100%; font-weight: bold; font-style: italic;
      }

      /* SIDEBAR ACEPTANTES */
      .sidebar-aceptantes {
        position: absolute; left: 120px; top: 0; bottom: 20px; width: 95px;
        border-right: 3px solid #000;
      }
      .sig-block {
        position: absolute; left: 0; right: 0; height: 38%;
        display: flex; align-items: center; justify-content: center;
      }
      .sig-block-top    { top: 3%; }
      .sig-block-bottom { bottom: 3%; }
      .sig-content {
        transform: rotate(-90deg); width: 200px;
        border-top: 2px solid #000; padding-top: 15px;
        display: flex; flex-direction: column; align-items: flex-end;
        font-size: 9px; line-height: 1.3;
      }

      /* MAIN CONTENT */
      .main-content {
        position: absolute; left: 215px; right: 0; top: 0; bottom: 20px;
        display: flex; flex-direction: column;
      }
      .header-row {
        display: flex; justify-content: space-between;
        padding: 15px 15px 5px 15px; font-size: 13px; font-weight: bold;
      }
      .data-table { width: 100%; border-collapse: collapse; border-top: 2px solid #000; border-bottom: 2px solid #000; }
      .data-table th, .data-table td { border: 2px solid #000; text-align: center; }
      .data-table th { font-size: 10px; padding: 2px; font-weight: bold; }
      .data-table td { font-size: 11px; font-weight: bold; padding: 6px; }
      .data-table th:first-child, .data-table td:first-child { border-left: none; }
      .data-table th:last-child,  .data-table td:last-child  { border-right: none; }
      .text-row-thin { padding: 4px 10px; font-size: 12px; border-bottom: 2px solid #000; }
      .split-row { display: flex; border-bottom: 2px solid #000; }
      .split-left { flex: 1.1; padding: 5px 10px; font-size: 12px; display: flex; flex-direction: column; }
      .split-right { flex: 0.9; display: flex; flex-direction: column; justify-content: flex-end; }
      .bank-table { width: 100%; border-collapse: collapse; border-top: 2px solid #000; border-left: 2px solid #000; }
      .bank-table th, .bank-table td { border: 2px solid #000; text-align: center; }
      .bank-table th { font-size: 10px; padding: 4px; }
      .bank-table td { height: 25px; font-size: 11px; }
      .bank-table th:last-child, .bank-table td:last-child { border-right: none; }
      .bank-table tr:last-child td { border-bottom: none; }
      .footer-row { display: flex; flex: 1; }
      .footer-left {
        flex: 1; padding: 5px 10px; font-size: 11px;
        border-right: 2px solid #000;
        display: flex; flex-direction: column; justify-content: space-between;
      }
      .footer-right { flex: 1; padding: 5px 10px; font-size: 12px; display: flex; flex-direction: column; justify-content: space-between; }
      .firma-underline { border-bottom: 1px solid #000; width: 80%; margin-bottom: 10px; }
      .firma-topline   { border-top: 1.5px solid #000; width: 95%; margin-bottom: 2px; }
      .footer-line {
        position: absolute; bottom: 0; left: 0; right: 0; height: 20px;
        border-top: 2px solid #000; font-size: 10px; font-weight: bold;
        padding-left: 10px; display: flex; align-items: center;
      }
    </style>
  </head>
  <body>

    {{-- Controles de pantalla (no se imprimen) --}}
    <div class="no-print" style="width:1050px; margin:0 auto 16px; display:flex; gap:8px;">
      <button onclick="window.print()" style="padding:8px 18px; background:#1d4ed8; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:14px;">
        🖨 Imprimir
      </button>
      <a href="{{ route('facturador.letras.show', $letra) }}"
         style="padding:8px 18px; background:#6b7280; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:14px; text-decoration:none;">
        ← Volver
      </a>
    </div>

    <div class="outer-wrapper">
      <div class="inner-container">

        {{-- Columna 1: Cláusulas --}}
        <div class="sidebar-clauses">
          <div class="text">
            <div style="margin-bottom: 10px">CLAUSULAS ESPECIALES:</div>
            <div style="margin-bottom: 20px">
              (1) En caso de mora, esta cambial generara las tasas de interes
              compensatorio y moratorio mas altas que la ley permita a su ultimo Tenedor.
            </div>
            <div style="margin-bottom: 20px">
              (2) El plazo de vencimiento podra ser prorrogado por el Tenedor,
              por el plazo que este señale sin que sea necesaria la intervencion
              del obligado principal y de los solidarios.
            </div>
            <div style="margin-bottom: 20px">
              (3) Esta Letra de Cambio no requiere ser protestada por falta de pago.
            </div>
            <div style="margin-bottom: 20px">
              (4) Su importe debe ser pagado solo en la misma moneda que expresa este titulo valor.
            </div>
          </div>
        </div>

        {{-- Columna 2: Aceptantes --}}
        <div class="sidebar-aceptantes">
          <div class="sig-block sig-block-top">
            <div class="sig-content">
              <div style="text-align:center;">
                <div>Aceptante</div>
                <div>{{ $letra->aceptante_representante ?? $letra->aceptante_nombre }}</div>
                <div>{{ $letra->aceptante_doi ?? 'D.O.I' }}</div>
              </div>
            </div>
          </div>
          <div class="sig-block sig-block-bottom">
            <div class="sig-content">
              <div style="text-align:center;">
                <div>Aceptante</div>
                <div>{{ $letra->aceptante_representante ?? $letra->aceptante_nombre }}</div>
                <div>{{ $letra->aceptante_doi ?? 'D.O.I' }}</div>
              </div>
            </div>
          </div>
        </div>

        {{-- Columna 3: Contenido Principal --}}
        <div class="main-content">

          {{-- Cabecera --}}
          <div class="header-row">
            <div style="font-weight:bold; font-style:italic; margin-left:100px;">
              {{ strtoupper($letra->tenedor_nombre) }}
            </div>
            <div style="font-weight:normal; font-size:11px;">
              {{ $letra->tenedor_domicilio ?? '' }}
            </div>
            <div style="font-weight:bold;">
              R.U.C.: {{ $letra->tenedor_ruc ?? '' }}
            </div>
          </div>

          {{-- Tabla de datos --}}
          <table class="data-table">
            <tr>
              <th rowspan="2">NUMERO</th>
              <th rowspan="2">REFERENCIA</th>
              <th colspan="3">FECHA DE GIRO</th>
              <th rowspan="2">LUGAR DE GIRO</th>
              <th colspan="3">VENCIMIENTO</th>
              <th rowspan="2">MONEDA E IMPORTE</th>
            </tr>
            <tr>
              <th>DIA</th>
              <th>MES</th>
              <th>ANO</th>
              <th>DIA</th>
              <th>MES</th>
              <th>ANO</th>
            </tr>
            <tr>
              <td>{{ $letra->numero_letra }}</td>
              <td>{{ $letra->referencia ?? '' }}</td>
              <td>{{ $letra->fecha_giro->format('d') }}</td>
              <td>{{ $letra->fecha_giro->format('m') }}</td>
              <td>{{ $letra->fecha_giro->format('Y') }}</td>
              <td>{{ strtoupper($letra->lugar_giro) }}</td>
              <td>{{ $letra->fecha_vencimiento->format('d') }}</td>
              <td>{{ $letra->fecha_vencimiento->format('m') }}</td>
              <td>{{ $letra->fecha_vencimiento->format('Y') }}</td>
              <td>
                @if($letra->codigo_moneda === 'USD') $ @elseif($letra->codigo_moneda === 'EUR') € @else S/ @endif
                {{ number_format($letra->monto, 2) }}
              </td>
            </tr>
          </table>

          {{-- Texto central --}}
          <div class="text-row-thin">
            Por esta <b>LETRA DE CAMBIO</b>, se servirá(n) pagar
            incondicionalmente a la Orden de: <b>{{ strtoupper($letra->tenedor_nombre) }}</b>
          </div>

          <div style="padding:4px 10px; font-size:13px; font-weight:bold; border-bottom:2px solid #000;">
            La cantidad de: {{ $letra->monto_letras }}..........................................
          </div>

          <div style="padding:4px 10px; font-size:11px; border-bottom:2px solid #000;">
            En el siguiente lugar de pago, o con cargo a la cuenta del Banco :
          </div>

          {{-- Área Banco/Aceptante --}}
          <div class="split-row">
            <div class="split-left">
              <b>Aceptante: {{ strtoupper($letra->aceptante_nombre) }}</b>
              <div style="margin-top:15px; font-size:11px;">
                Domicilio: {{ $letra->aceptante_domicilio ?? '' }}<br>
                <b>R.U.C : {{ $letra->aceptante_ruc ?? '' }}</b>
                @if($letra->aceptante_telefono)
                  <span style="margin-left:60px;">Teléfono: {{ $letra->aceptante_telefono }}</span>
                @endif
              </div>
            </div>
            <div class="split-right">
              <div style="text-align:right; padding:2px 10px; font-size:10px;">
                Importe a debitar en la siguiente cuenta del banco que se indica
              </div>
              <table class="bank-table">
                <tr>
                  <th>BANCO</th>
                  <th>OFICINA</th>
                  <th>CUENTA</th>
                  <th>D.C</th>
                </tr>
                <tr>
                  <td>{{ $letra->banco ?? '' }}</td>
                  <td>{{ $letra->banco_oficina ?? '' }}</td>
                  <td>{{ $letra->banco_cuenta ?? '' }}</td>
                  <td>{{ $letra->banco_dc ?? '' }}</td>
                </tr>
              </table>
            </div>
          </div>

          {{-- Firmas --}}
          <div class="footer-row">
            <div class="footer-left">
              <div>
                Aval Permanente<br>
                Domicilio<br>
                Localidad<br>
                D.O.I <span style="margin-left:120px">Firma</span><br>
                Telefono
              </div>
              <div class="firma-underline"></div>
            </div>
            <div class="footer-right">
              <div>
                <b>{{ strtoupper($letra->tenedor_nombre) }}</b><br>
                <b>R.U.C.: {{ $letra->tenedor_ruc ?? '' }}</b>
              </div>
              <div style="margin-bottom:10px;">
                <div class="firma-topline"></div>
                <b>FIRMA</b><br>
                Nombre del Representante:<br><br>
                D.O.I
              </div>
            </div>
          </div>
        </div>

        {{-- Línea final --}}
        <div class="footer-line">
          NO ESCRIBIR NI FIRMAR DEBAJO DE ESTA LINEA
        </div>
      </div>
    </div>

  </body>
</html>
