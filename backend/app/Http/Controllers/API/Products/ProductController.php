<?php

namespace App\Http\Controllers\API\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Resources\Products\ProductResource;
use App\Models\Product;
use App\Services\Products\ProductService;
use App\Support\ListSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Thin controller: validate (Form Request) → Service → Resource.
class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    // GET /products?search=(name, sku, exact barcode)&category_id=&active=1|0&per_page=&page=&sort=&order=
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'active' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ] + ListSort::rules(ProductService::SORTS));

        return ProductResource::collection($this->products->paginate($filters));
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($this->products->find($product));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        return (new ProductResource($this->products->create($request->validated())))->response()->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        return new ProductResource($this->products->update($product, $request->validated()));
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->products->delete($product);

        return response()->json(['message' => 'Product deleted.']);
    }
}
