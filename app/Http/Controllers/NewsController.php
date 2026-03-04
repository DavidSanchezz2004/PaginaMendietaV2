<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Company;
use App\Services\News\NewsService;
use App\Http\Requests\News\StoreNewsRequest;
use App\Http\Requests\News\UpdateNewsRequest;
use Illuminate\Http\Request;
use App\Services\Company\ActiveCompanyService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class NewsController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = $request->user();
        abort_if(!$user, 403);

        $this->authorize('viewAny', News::class);
        
        $news = News::query()->latest()->paginate(12);

        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        $this->authorize('create', News::class);
        return view('admin.news.create');
    }

    public function store(StoreNewsRequest $request, NewsService $newsService)
    {
        $this->authorize('create', News::class);
        
        $newsService->storeNews(
            $request->validated(), 
            $request->file('image'), 
            $request->user()->id
        );

        return redirect()->route('news.index')->with('status', 'Noticia creada correctamente.');
    }

    public function show(News $news)
    {
        $this->authorize('view', $news);
        return view('admin.news.show', compact('news'));
    }

    public function edit(News $news)
    {
        $this->authorize('update', $news);
        return view('admin.news.edit', compact('news'));
    }

    public function update(UpdateNewsRequest $request, News $news, NewsService $newsService)
    {
        $this->authorize('update', $news);
        
        $newsService->updateNews(
            $news, 
            $request->validated(), 
            $request->file('image')
        );

        return redirect()->route('news.index')->with('status', 'Noticia actualizada correctamente.');
    }

    public function destroy(News $news)
    {
        $this->authorize('delete', $news);
        
        if ($news->image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($news->image_path);
        }
        
        $news->delete();

        return redirect()->route('news.index')->with('status', 'Noticia eliminada correctamente.');
    }
}
