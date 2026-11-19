<?php

namespace App\Http\Controllers\API\Register;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

// GET /register/catalog → everything the sell screen needs in one call: active categories, active
// products (price in centavos, tax class), and the active store locations to sell from.
// Loads the whole active catalog: fine for small/medium stores; a large catalog will need
// search-as-you-type instead (noted in the roadmap).
class RegisterCatalogController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'categories' => Category::where('active', true)->orderBy('name')->get(['id', 'name']),
            'products' => Product::where('active', true)->orderBy('name')
                ->get(['id', 'sku', 'barcode', 'name', 'category_id', 'unit', 'price', 'tax_class', 'sc_pwd_eligible', 'track_stock']),
            'locations' => Location::where('active', true)->where('type', 'store')->orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }
}
