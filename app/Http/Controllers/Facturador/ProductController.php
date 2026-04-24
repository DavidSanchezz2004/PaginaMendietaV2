<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facturador\StoreProductRequest;
use App\Http\Requests\Facturador\UpdateProductRequest;
use App\Models\Product;
use App\Services\Facturador\ProductService;
use Illuminate\Http\JsonResponse;
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

    public function store(StoreProductRequest $request): RedirectResponse|JsonResponse
    {
        // authorize() ya corre en StoreProductRequest::authorize()
        $product = $this->service->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => 'Producto creado correctamente.',
                'product' => [
                    'id'                       => $product->id,
                    'codigo_interno'           => $product->codigo_interno,
                    'codigo_sunat'             => $product->codigo_sunat,
                    'tipo'                     => $product->tipo,
                    'descripcion'              => $product->descripcion,
                    'codigo_unidad_medida'     => $product->codigo_unidad_medida,
                    'precio_unitario'          => (float) $product->precio_unitario,
                    'valor_unitario'           => (float) $product->valor_unitario,
                    'codigo_indicador_afecto'  => $product->codigo_indicador_afecto,
                ],
            ], 201);
        }

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
