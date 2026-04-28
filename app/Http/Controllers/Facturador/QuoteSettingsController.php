<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class QuoteSettingsController extends Controller
{
    public function edit(): View
    {
        [$company, $settings] = $this->settingsForActiveCompany();

        return view('facturador.quotations.settings', [
            'company' => $company,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        [$company, $settings] = $this->settingsForActiveCompany();

        $data = $request->validate([
            'quote_enabled' => ['nullable', 'boolean'],
            'remove_logo' => ['nullable', 'boolean'],
            'quote_logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'company_name' => ['nullable', 'string', 'max:200'],
            'ruc' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:300'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:200'],
            'website' => ['nullable', 'string', 'max:300'],
            'show_igv_breakdown' => ['nullable', 'boolean'],
            'show_bank_accounts' => ['nullable', 'boolean'],
            'quote_terms' => ['nullable', 'string', 'max:4000'],
            'quote_footer' => ['nullable', 'string', 'max:4000'],
            'quote_thanks_message' => ['nullable', 'string', 'max:1000'],
            'bank_accounts' => ['nullable', 'array'],
            'bank_accounts.*.banco' => ['nullable', 'string', 'max:80'],
            'bank_accounts.*.titular' => ['nullable', 'string', 'max:160'],
            'bank_accounts.*.moneda' => ['nullable', 'string', 'max:10'],
            'bank_accounts.*.cuenta' => ['nullable', 'string', 'max:80'],
            'bank_accounts.*.cci' => ['nullable', 'string', 'max:80'],
            'bank_accounts.*.icon_base64' => ['nullable', 'string'],
            'bank_account_icons' => ['nullable', 'array'],
            'bank_account_icons.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $uploadedIcons = $request->file('bank_account_icons', []);

        $paymentInfo = collect($data['bank_accounts'] ?? [])
            ->map(function (array $account, int $index) use ($uploadedIcons) {
                $iconBase64 = $account['icon_base64'] ?? null;
                $iconFile = $uploadedIcons[$index] ?? null;

                if ($iconFile) {
                    $iconBase64 = sprintf(
                        'data:%s;base64,%s',
                        $iconFile->getMimeType() ?: 'image/png',
                        base64_encode(file_get_contents($iconFile->getRealPath()))
                    );
                }

                return [
                    'banco' => trim((string) ($account['banco'] ?? '')),
                    'titular' => trim((string) ($account['titular'] ?? '')),
                    'moneda' => trim((string) ($account['moneda'] ?? 'PEN')) ?: 'PEN',
                    'cuenta' => trim((string) ($account['cuenta'] ?? '')),
                    'cci' => trim((string) ($account['cci'] ?? '')),
                    'icon_base64' => $iconBase64,
                ];
            })
            ->filter(fn (array $account) => $account['banco'] !== '' || $account['cuenta'] !== '' || $account['cci'] !== '')
            ->values()
            ->all();

        $payload = Arr::only($data, [
            'primary_color',
            'secondary_color',
            'company_name',
            'ruc',
            'address',
            'phone',
            'email',
            'website',
            'quote_terms',
            'quote_footer',
            'quote_thanks_message',
        ]);

        $payload['quote_enabled'] = $request->boolean('quote_enabled');
        $payload['show_igv_breakdown'] = $request->boolean('show_igv_breakdown');
        $payload['show_bank_accounts'] = $request->boolean('show_bank_accounts');
        $payload['quote_payment_info'] = $paymentInfo;
        $payload['bank_accounts'] = $paymentInfo;

        if ($request->boolean('remove_logo')) {
            $payload['quote_logo_base64'] = null;
        }

        if ($request->hasFile('quote_logo_file')) {
            $file = $request->file('quote_logo_file');
            $payload['quote_logo_base64'] = sprintf(
                'data:%s;base64,%s',
                $file->getMimeType() ?: 'image/png',
                base64_encode(file_get_contents($file->getRealPath()))
            );
        }

        $settings->fill($payload);
        $settings->company_id = $company->id;
        $settings->save();

        return redirect()
            ->route('facturador.quote-settings.edit')
            ->with('success', 'Configuración del cotizador guardada.');
    }

    private function settingsForActiveCompany(): array
    {
        $company = Company::findOrFail((int) session('company_id'));

        $settings = CompanySetting::firstOrCreate(
            ['company_id' => $company->id],
            [
                'quote_enabled' => true,
                'primary_color' => '#013b33',
                'secondary_color' => '#eef7f5',
                'company_name' => $company->name,
                'ruc' => $company->ruc,
            ]
        );

        return [$company, $settings];
    }
}
