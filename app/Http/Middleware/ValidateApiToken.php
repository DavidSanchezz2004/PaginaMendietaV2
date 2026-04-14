<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentica llamadas de n8n mediante un Bearer token estático por empresa.
 *
 * Uso en n8n: Header "Authorization: Bearer {api_token}"
 *             O query param: ?api_token={token}
 *
 * Tras autenticar, inyecta company_id en el request para que los
 * servicios puedan operar con scope correcto (sin sesión de usuario web).
 */
class ValidateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (! $token) {
            return response()->json([
                'error'   => 'No autorizado.',
                'message' => 'Se requiere Bearer token en el header Authorization.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $company = Company::where('api_token', $token)->first();

        if (! $company) {
            return response()->json([
                'error'   => 'Token inválido.',
                'message' => 'El token proporcionado no corresponde a ninguna empresa.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Inyectar company en el request para los controllers de API
        $request->merge(['_api_company_id' => $company->id]);
        $request->attributes->set('api_company', $company);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        // 1. Header Authorization: Bearer xxx
        $authHeader = $request->header('Authorization', '');
        if (str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // 2. Query param fallback (útil para testing con GET)
        $queryToken = $request->query('api_token');
        if ($queryToken && is_string($queryToken)) {
            return $queryToken;
        }

        return null;
    }
}
