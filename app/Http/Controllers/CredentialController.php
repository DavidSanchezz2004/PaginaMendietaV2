<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Company;
use App\Models\Credential;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Credential::class);
        $user = $request->user();

        $query = Credential::with('company');

        if ($user->role === RoleEnum::CLIENT || $user->role === RoleEnum::AUXILIAR) {
            $activeCompanyId = session('company_id') ?? $user->companies()->first()?->id;
            abort_if(!$activeCompanyId, 403, 'Aún no perteneces a ninguna empresa.');
            
            $query->where('company_id', $activeCompanyId);
        } else {
            // Admin filtrando por empresa
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        }

        $credentials = $query->orderBy('platform', 'asc')->get();

        $companies = collect();
        if ($user->role === RoleEnum::ADMIN || $user->role === RoleEnum::SUPERVISOR) {
            $companies = Company::orderBy('name')->get();
        }

        return view('admin.credentials.index', compact('credentials', 'companies'));
    }

    public function create()
    {
        $this->authorize('create', Credential::class);
        $companies = Company::orderBy('name')->get();
        return view('admin.credentials.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Credential::class);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'platform' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        Credential::create($validated);

        return redirect()->route('credentials.index')
            ->with('status', '✅ Credencial almacenada con éxito (cifrada).');
    }

    public function edit(Credential $credential)
    {
        $this->authorize('update', $credential);
        $companies = Company::orderBy('name')->get();
        return view('admin.credentials.edit', compact('credential', 'companies'));
    }

    public function update(Request $request, Credential $credential)
    {
        $this->authorize('update', $credential);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'platform' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string', // A veces solo quieren actualizar notas
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $credential->update($validated);

        return redirect()->route('credentials.index')
            ->with('status', '🔄 Credencial modificada.');
    }

    public function destroy(Credential $credential)
    {
        $this->authorize('delete', $credential);
        $credential->delete();

        return redirect()->route('credentials.index')
            ->with('status', '🗑️ Credencial destruida permanentemente.');
    }
}
