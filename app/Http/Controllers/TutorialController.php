<?php

namespace App\Http\Controllers;

use App\Models\Tutorial;
use App\Models\Company;
use App\Services\Tutorial\TutorialService;
use App\Http\Requests\Tutorial\StoreTutorialRequest;
use App\Http\Requests\Tutorial\UpdateTutorialRequest;
use Illuminate\Http\Request;
use App\Services\Company\ActiveCompanyService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TutorialController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = $request->user();
        abort_if(!$user, 403);

        $this->authorize('viewAny', Tutorial::class);
        
        $tutorials = Tutorial::query()->latest()->paginate(12);

        return view('admin.tutorials.index', compact('tutorials'));
    }

    public function create()
    {
        $this->authorize('create', Tutorial::class);
        return view('admin.tutorials.create');
    }

    public function store(StoreTutorialRequest $request, TutorialService $tutorialService)
    {
        $this->authorize('create', Tutorial::class);
        
        $tutorialService->storeTutorial(
            $request->validated(), 
            $request->user()->id
        );

        return redirect()->route('tutorials.index')->with('status', 'Tutorial creado correctamente.');
    }

    public function show(Tutorial $tutorial)
    {
        $this->authorize('view', $tutorial);
        return view('admin.tutorials.show', compact('tutorial'));
    }

    public function edit(Tutorial $tutorial)
    {
        $this->authorize('update', $tutorial);
        return view('admin.tutorials.edit', compact('tutorial'));
    }

    public function update(UpdateTutorialRequest $request, Tutorial $tutorial, TutorialService $tutorialService)
    {
        $this->authorize('update', $tutorial);
        
        $tutorialService->updateTutorial(
            $tutorial, 
            $request->validated()
        );

        return redirect()->route('tutorials.index')->with('status', 'Tutorial actualizado correctamente.');
    }

    public function destroy(Tutorial $tutorial)
    {
        $this->authorize('delete', $tutorial);
        
        $tutorial->delete();

        return redirect()->route('tutorials.index')->with('status', 'Tutorial eliminado correctamente.');
    }
}
