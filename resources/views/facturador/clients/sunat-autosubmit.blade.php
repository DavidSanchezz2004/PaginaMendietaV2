<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciando sesión en SUNAT...</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 48px 40px;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            max-width: 420px;
            width: 90%;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #1e3a5f;
            color: #fff;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 999px;
            margin-bottom: 28px;
        }

        .spinner {
            width: 52px;
            height: 52px;
            border: 5px solid #e2e8f0;
            border-top-color: #1e3a5f;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 28px;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        h2 {
            color: #1e293b;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #64748b;
            font-size: .9rem;
            line-height: 1.6;
            margin-bottom: 6px;
        }

        .client-name {
            color: #1e3a5f;
            font-size: .85rem;
            font-weight: 600;
            margin-top: 6px;
        }

        .note {
            margin-top: 24px;
            font-size: .75rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    {{--
        Formulario invisible que se envía automáticamente con las credenciales SOL.
        El POST va directamente al servidor de SUNAT desde el navegador del usuario,
        por lo que SUNAT setea sus propias cookies de sesión sin problemas de
        Same-Origin Policy.
    --}}
    <form
        id="sunat-form"
        method="POST"
        action="https://api-seguridad.sunat.gob.pe/v1/clientessol/59d39217-c025-4de5-b342-393b0f4630ab/oauth2/loginMenuSol"
        style="display:none;"
    >
        <input type="hidden" name="txtRuc"        value="{{ $ruc }}">
        <input type="hidden" name="txtUsuario"    value="{{ $usuario_sol }}">
        <input type="hidden" name="txtContrasena" value="{{ $clave_sol }}">
        <input type="hidden" name="lang"          value="es-PE">
        <input type="hidden" name="showDni"       value="true">
        <input type="hidden" name="showLanguages" value="false">
        <input type="hidden" name="originalUrl"   value="https://e-menu.sunat.gob.pe/cl-ti-itmenu2/AutenticaMenuInternetPlataforma.htm">
    </form>

    {{-- UI de espera visible mientras el formulario se envía --}}
    <div class="card">
        <div class="badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
            </svg>
            SUNAT SOL
        </div>

        <div class="spinner"></div>

        <h2>Iniciando sesión en SUNAT...</h2>
        <p class="subtitle">Por favor espere.<br>Será redirigido automáticamente al portal.</p>
        <p class="client-name">{{ $nombre }}</p>
        <p class="note">RUC {{ $ruc }}</p>
    </div>

    <script>
        // Autosubmit inmediato al cargar la página
        window.addEventListener('load', function () {
            document.getElementById('sunat-form').submit();
        });
    </script>

</body>
</html>
