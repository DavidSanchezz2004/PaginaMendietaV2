<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\CompanySetting;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controlador para vista pública de cotizaciones.
 * 
 * Accesible SIN autenticación, usando token UUID.
 * Rutas:
 *  - GET /quotes/{token} → Vista pública
 *  - GET /quotes/{token}/pdf → Descarga PDF
 *  - POST /quotes/{token}/accept → Cliente acepta
 *  - POST /quotes/{token}/reject → Cliente rechaza
 */
class QuoteClientController extends Controller
{
    /**
     * Vista pública de cotización para cliente.
     * GET /quotes/{token}
     */
    public function show(string $token): View|Response
    {
        $quote = Quote::where('share_token', $token)
            ->with('items', 'client', 'company')
            ->firstOrFail();

        // Verificar que no esté vencida (si aplica)
        if ($quote->getEstaVencidaAttribute()) {
            return response()->view('errors.quote-expired', [
                'quote' => $quote,
            ], 410); // 410 Gone
        }

        // Obtener configuración de empresa para branding
        $settings = CompanySetting::where('company_id', $quote->company_id)->first();

        return view('facturador.cotizaciones.client', [
            'quote' => $quote,
            'settings' => $settings,
        ]);
    }

    /**
     * Genera PDF de la cotización para descargar.
     * GET /quotes/{token}/pdf
     */
    public function pdf(string $token)
    {
        $quote = Quote::where('share_token', $token)
            ->with('items', 'client', 'company')
            ->firstOrFail();

        $settings = CompanySetting::where('company_id', $quote->company_id)->first();

        // Renderizar la misma vista pero con formato PDF
        $pdf = \PDF::loadView('facturador.cotizaciones.client-pdf', [
            'quote' => $quote,
            'settings' => $settings,
        ]);

        return $pdf->download("Cotización-{$quote->numero_cotizacion}.pdf");
    }

    /**
     * Cliente acepta la cotización.
     * POST /quotes/{token}/accept
     */
    public function accept(string $token)
    {
        $quote = Quote::where('share_token', $token)
            ->firstOrFail();

        if ($quote->estado !== 'sent') {
            return back()->with('error', 'Esta cotización ya ha sido procesada.');
        }

        $quote->estado = 'accepted';
        $quote->accepted_at = now();
        $quote->save();

        // TODO: Enviar notificación a empresa vendedora

        return back()->with('success', 'Cotización aceptada. La empresa se pondrá en contacto pronto.');
    }

    /**
     * Cliente rechaza la cotización.
     * POST /quotes/{token}/reject
     */
    public function reject(string $token)
    {
        $quote = Quote::where('share_token', $token)
            ->firstOrFail();

        if (in_array($quote->estado, ['accepted', 'rejected'])) {
            return back()->with('error', 'Esta cotización ya ha sido procesada.');
        }

        $quote->estado = 'rejected';
        $quote->rejected_at = now();
        $quote->save();

        // TODO: Enviar notificación a empresa vendedora

        return back()->with('info', 'Cotización rechazada. Gracias por considerarla.');
    }
}
