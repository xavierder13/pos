<?php

namespace App\Http\Controllers\API\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockDocumentRequest;
use App\Http\Resources\Inventory\StockDocumentResource;
use App\Http\Resources\Inventory\StockMovementResource;
use App\Models\Product;
use App\Models\StockDocument;
use App\Models\StockLevel;
use App\Services\Inventory\InventoryService;
use App\Support\ListSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Stock on hand, the movement ledger, stock documents, and the three postings (receive / adjust / transfer).
class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    // GET /inventory/stock?location_id=&search=&category_id=&status=low|out|negative&per_page=&page=&sort=sku|name|quantity&order=
    public function stock(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'location_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:low,out,negative'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ] + ListSort::rules(InventoryService::STOCK_SORTS));

        $page = $this->inventory->stock($filters);

        return response()->json([
            'data' => $page->getCollection()->map(fn (Product $p) => [
                'id' => $p->id,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'name' => $p->name,
                'unit' => $p->unit,
                'category' => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
                'quantity' => (float) $p->quantity,
                'reorder_level' => (float) $p->reorder_level,
                'cost' => $p->cost,
                'active' => (bool) $p->active,
            ]),
            'meta' => ['current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total()],
        ]);
    }

    // GET /inventory/low-stock?location_id=  (dashboard: up to 8 product × location pairs, emptiest first)
    public function lowStock(Request $request): JsonResponse
    {
        $request->validate(['location_id' => ['nullable', 'integer']]);
        $items = $this->inventory->lowStock($request->integer('location_id') ?: null);

        return response()->json(['data' => $items->map(fn (StockLevel $l) => [
            'id' => $l->product->id, 'sku' => $l->product->sku, 'name' => $l->product->name, 'unit' => $l->product->unit,
            'location' => ['id' => $l->location->id, 'code' => $l->location->code, 'name' => $l->location->name],
            'quantity' => (float) $l->quantity, 'reorder_level' => (float) $l->product->reorder_level,
        ])]);
    }

    // GET /inventory/products/{product}/levels → quantity per location
    public function levels(Product $product): JsonResponse
    {
        return response()->json(['data' => $this->inventory->levelsFor($product)]);
    }

    // GET /inventory/movements?product_id=&location_id=&type=&date_from=&date_to=&per_page=&page=
    public function movements(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'product_id' => ['nullable', 'integer'],
            'location_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'in:receipt,adjustment,transfer,opening'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return StockMovementResource::collection($this->inventory->movements($filters));
    }

    // GET /inventory/documents?type=&location_id=&search=&per_page=&page=
    public function documents(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'type' => ['nullable', 'in:receipt,adjustment,transfer,opening'],
            'location_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return StockDocumentResource::collection(
            $this->inventory->documentsQuery($filters)->paginate(min((int) ($filters['per_page'] ?? 20), 100)),
        );
    }

    public function document(StockDocument $document): StockDocumentResource
    {
        return new StockDocumentResource($this->inventory->document($document));
    }

    public function receive(StockDocumentRequest $request): JsonResponse
    {
        return $this->created($this->inventory->receive($request->validated(), $request->user()->id));
    }

    public function adjust(StockDocumentRequest $request): JsonResponse
    {
        return $this->created($this->inventory->adjust($request->validated(), $request->user()->id));
    }

    public function transfer(StockDocumentRequest $request): JsonResponse
    {
        return $this->created($this->inventory->transfer($request->validated(), $request->user()->id));
    }

    private function created(StockDocument $doc): JsonResponse
    {
        return (new StockDocumentResource($doc))->additional(['message' => "{$doc->number} posted."])->response()->setStatusCode(201);
    }
}
