<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\FinalDocument\StoreFinalDocumentRequest;
use App\Models\Company;
use App\Models\FinalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FinalDocumentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', FinalDocument::class);
        $user = $request->user();

        $query = FinalDocument::with('company')->latest();

        if ($user->role === RoleEnum::CLIENT || $user->role === RoleEnum::AUXILIAR) {
            $activeCompanyId = session('company_id') ?? $user->companies()->first()?->id;
            abort_if(!$activeCompanyId, 403, 'Aún no perteneces a ninguna empresa.');
            
            $query->where('company_id', $activeCompanyId);
        }

        // Filtering by document type if provided
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        $documents = $query->paginate(15)->withQueryString();
        
        return view('admin.final_documents.index', compact('documents'));
    }

    public function create()
    {
        $this->authorize('create', FinalDocument::class);
        $companies = Company::orderBy('name')->get();
        return view('admin.final_documents.create', compact('companies'));
    }

    public function store(StoreFinalDocumentRequest $request)
    {
        $this->authorize('create', FinalDocument::class);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path = $file->store('final_documents', 'public');

        FinalDocument::create([
            'title' => $request->title,
            'document_type' => $request->document_type,
            'file_path' => $path,
            'original_name' => $originalName,
            'company_id' => $request->company_id,
        ]);

        return redirect()->route('final-documents.index')
            ->with('status', 'Documento final subido exitosamente.');
    }

    public function destroy(FinalDocument $finalDocument)
    {
        $this->authorize('delete', $finalDocument);

        if ($finalDocument->file_path && Storage::disk('public')->exists($finalDocument->file_path)) {
            Storage::disk('public')->delete($finalDocument->file_path);
        }

        $finalDocument->delete();

        return redirect()->route('final-documents.index')
            ->with('status', 'Documento eliminado exitosamente.');
    }

    public function download(FinalDocument $finalDocument)
    {
        $this->authorize('view', $finalDocument);

        abort_if(!Storage::disk('public')->exists($finalDocument->file_path), 404, 'Archivo no encontrado.');

        return Storage::disk('public')->download($finalDocument->file_path, $finalDocument->original_name);
    }
}
