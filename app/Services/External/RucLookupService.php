<?php

namespace App\Services\External;

use Illuminate\Support\Arr;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RucLookupService
{
    /**
     * @return array<string, mixed>
     */
    public function lookup(string $ruc): array
    {
        $baseUrl = (string) (config('services.aqpfact.base_url') ?: env('AQPFACT_BASE_URL', 'https://apis.aqpfact.pe/api'));
        $token = (string) (config('services.aqpfact.token') ?: env('AQPFACT_TOKEN'));
        $verifySsl = filter_var(config('services.aqpfact.verify_ssl', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $verifySsl = $verifySsl ?? true;

        if ($baseUrl === '' || $token === '') {
            throw new RuntimeException('No se configuró la integración de consulta RUC. Define AQPFACT_TOKEN en el archivo .env.');
        }

        $request = Http::baseUrl($baseUrl)
            ->acceptJson()
            ->withToken($token)
            ->timeout(12)
            ->withOptions(['verify' => $verifySsl]);

        try {
            $response = $request->get('/ruc/'.$ruc);
        } catch (ConnectionException $exception) {
            $message = $exception->getMessage();
            $isSslIssue = str_contains($message, 'cURL error 60') || str_contains(strtolower($message), 'ssl certificate');

            if ($isSslIssue && app()->environment('local')) {
                $response = Http::baseUrl($baseUrl)
                    ->acceptJson()
                    ->withToken($token)
                    ->timeout(12)
                    ->withOptions(['verify' => false])
                    ->get('/ruc/'.$ruc);
            } else {
                throw new RuntimeException('Error de conexión consultando el servicio RUC.', previous: $exception);
            }
        }

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo consultar el RUC en el servicio externo.');
        }

        $payload = $response->json();
        $success = (bool) Arr::get($payload, 'success', false);
        $data = Arr::get($payload, 'data');

        if (! $success || ! is_array($data)) {
            throw new RuntimeException('La consulta RUC devolvió una respuesta inválida.');
        }

        return [
            'ruc' => (string) Arr::get($data, 'ruc', $ruc),
            'name' => (string) Arr::get($data, 'nombre_o_razon_social', Arr::get($data, 'name', '')),
            'direccion' => (string) Arr::get($data, 'direccion', Arr::get($data, 'address', '')),
            'departamento' => (string) Arr::get($data, 'departamento', ''),
            'provincia' => (string) Arr::get($data, 'provincia', ''),
            'distrito' => (string) Arr::get($data, 'distrito', ''),
            'estado' => (string) Arr::get($data, 'estado', Arr::get($data, 'state', '')),
        ];
    }

    /**
     * Consulta DNI en AQPFact y devuelve datos básicos del titular.
     *
     * @return array<string, string>
     */
    public function lookupDni(string $dni): array
    {
        $baseUrl  = (string) (config('services.aqpfact.base_url') ?: env('AQPFACT_BASE_URL', 'https://apis.aqpfact.pe/api'));
        $token    = (string) (config('services.aqpfact.token') ?: env('AQPFACT_TOKEN'));
        $verifySsl = filter_var(config('services.aqpfact.verify_ssl', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $verifySsl = $verifySsl ?? true;

        if ($baseUrl === '' || $token === '') {
            throw new RuntimeException('No se configuró la integración de consulta DNI. Define AQPFACT_TOKEN en el archivo .env.');
        }

        $request = Http::baseUrl($baseUrl)
            ->acceptJson()
            ->withToken($token)
            ->timeout(12)
            ->withOptions(['verify' => $verifySsl]);

        try {
            $response = $request->get('/dni/'.$dni);
        } catch (ConnectionException $exception) {
            $message   = $exception->getMessage();
            $isSslIssue = str_contains($message, 'cURL error 60') || str_contains(strtolower($message), 'ssl certificate');

            if ($isSslIssue && app()->environment('local')) {
                $response = Http::baseUrl($baseUrl)
                    ->acceptJson()
                    ->withToken($token)
                    ->timeout(12)
                    ->withOptions(['verify' => false])
                    ->get('/dni/'.$dni);
            } else {
                throw new RuntimeException('Error de conexión consultando el servicio DNI.', previous: $exception);
            }
        }

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo consultar el DNI en el servicio externo.');
        }

        $payload = $response->json();
        $success = (bool) Arr::get($payload, 'success', false);
        $data    = Arr::get($payload, 'data');

        if (! $success || ! is_array($data)) {
            throw new RuntimeException('La consulta DNI devolvió una respuesta inválida.');
        }

        return [
            'dni'              => (string) Arr::get($data, 'numero', $dni),
            'nombre'           => (string) Arr::get($data, 'nombre_completo', Arr::get($data, 'name', '')),
            'nombres'          => (string) Arr::get($data, 'nombres', ''),
            'apellido_paterno' => (string) Arr::get($data, 'apellido_paterno', ''),
            'apellido_materno' => (string) Arr::get($data, 'apellido_materno', ''),
        ];
    }}