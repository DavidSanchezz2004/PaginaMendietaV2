<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facturador\StoreProductRequest;
use App\Http\Requests\Facturador\UpdateProductRequest;
use App\Models\Product;
use App\Services\Facturador\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * CRUD de Productos del Facturador.
 * Controller delgado: toda la lógica en ProductService.
 * Políticas verificadas en FormRequests (authorize()) y explícitas ($this->authorize()).
 */
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $service,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->service->paginate();

        return view('facturador.products.index', compact('products'));
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('facturador.products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        // authorize() ya corre en StoreProductRequest::authorize()
        $this->service->create($request->validated());

        return redirect()->route('facturador.products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('facturador.products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->service->update($product, $request->validated());

        return redirect()->route('facturador.products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->service->deactivate($product);

        return redirect()->route('facturador.products.index')
            ->with('success', 'Producto desactivado.');
    }
}
