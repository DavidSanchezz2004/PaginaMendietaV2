<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class FeasyConfigController extends Controller
{
    /**
     * Muestra el formulario de configuración del token global de Feasy.
     */
    public function edit(): View
    {
        $token    = config('services.feasy.token', '');
        $hasToken = ! empty($token);
        $tokenLen = $hasToken ? strlen($token) : 0;

        return view('admin.feasy-config', compact('hasToken', 'tokenLen'));
    }

    /**
     * Graba el token en el archivo .env y limpia la caché de configuración.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'feasy_token' => ['nullable', 'string', 'max:1000'],
        ]);

        $token   = trim($request->input('feasy_token', ''));
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return back()->withErrors(['feasy_token' => 'No se encontró el archivo .env.']);
        }

        $content = file_get_contents($envPath);

        // Si ya existe la línea FEASY_TOKEN=, reemplazarla; si no, añadirla al final.
        if (preg_match('/^FEASY_TOKEN=/m', $content)) {
            $content = preg_replace('/^FEASY_TOKEN=.*/m', 'FEASY_TOKEN=' . $token, $content);
        } else {
            $content = rtrim($content) . "\nFEASY_TOKEN=" . $token . "\n";
        }

        file_put_contents($envPath, $content);

        // Limpiar caché de configuración para que el cambio surta efecto de inmediato.
        Artisan::call('config:clear');

        return back()->with('success', 'Token Feasy actualizado correctamente.');
    }
}