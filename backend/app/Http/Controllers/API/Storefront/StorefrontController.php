<?php

namespace App\Http\Controllers\API\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// GET /storefront?location=MAIN — PUBLIC (no sign-in, throttled): what the store's own website shows.
// Only customer-facing fields: names, selling prices (centavos), SC/PWD eligibility and an availability
// bucket at one store. Never costs, barcodes, exact quantities, warehouses or inactive records.
class StorefrontController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate(['location' => ['nullable', 'string', 'max:20']]);

        $stores = Location::where('active', true)->where('type', 'store')->orderBy('name')->get(['id', 'code', 'name', 'address']);
        $store = $stores->firstWhere('code', strtoupper((string) $request->query('location'))) ?? $stores->first();

        $levels = $store
            ? StockLevel::where('location_id', $store->id)->pluck('quantity', 'product_id')
            : collect();

        $products = Product::where('active', true)->orderBy('name')
            ->get(['id', 'name', 'category_id', 'unit', 'price', 'sc_pwd_eligible', 'track_stock', 'reorder_level'])
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category_id' => $p->category_id,
                'unit' => $p->unit,
                'price' => $p->price,
                'sc_pwd_eligible' => $p->sc_pwd_eligible,
                // made to order / services: always available; stocked items: a bucket, never the count
                'availability' => $p->track_stock ? $this->bucket((float) ($levels[$p->id] ?? 0), (float) $p->reorder_level) : 'available',
            ]);

        return response()->json([
            'store' => $store ? ['code' => $store->code, 'name' => $store->name, 'address' => $store->address] : null,
            'stores' => $stores->map(fn (Location $l) => ['code' => $l->code, 'name' => $l->name, 'address' => $l->address]),
            'categories' => Category::where('active', true)->orderBy('name')->get(['id', 'name']),
            'products' => $products,
            'generated_at' => now()->toIso8601String(),
        ])->header('Cache-Control', 'public, max-age=60');
    }

    private function bucket(float $qty, float $reorder): string
    {
        if ($qty <= 0) {
            return 'sold_out';
        }

        return $qty <= $reorder ? 'few_left' : 'in_stock';
    }
}
