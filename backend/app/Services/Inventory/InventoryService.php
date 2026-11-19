<?php

namespace App\Services\Inventory;

use App\Exceptions\BusinessRuleException;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockDocument;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Support\ListSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// The stock ledger (pos-domain → areas/catalog-inventory.md). Every change is a StockDocument with
// StockMovement lines, written with the StockLevel cache in ONE transaction, the level row locked
// (lockForUpdate) so two tills can't lose an update. Posted documents are final: a mistake is fixed
// by another adjustment. Stock may go negative (sales must not stop): it is flagged, not refused,
// except for adjustments and transfers, which a person types and can correct.
class InventoryService
{
    public const STOCK_SORTS = ['sku', 'name', 'quantity'];

    // ── reading ──────────────────────────────────────────────────

    /**
     * Products with their quantity at one location (or summed over all), stock-tracked only.
     *
     * @param  array<string, mixed>  $filters  location_id?, search?, category_id?, status? (low|out|negative), sort, order
     */
    public function stock(array $filters): LengthAwarePaginator
    {
        $locationId = $filters['location_id'] ?? null;
        $levels = StockLevel::query()
            ->selectRaw('product_id, SUM(quantity) as quantity')
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->groupBy('product_id');

        $query = Product::query()
            ->select('products.id', 'products.sku', 'products.barcode', 'products.name', 'products.unit', 'products.category_id',
                'products.reorder_level', 'products.cost', 'products.price', 'products.active')
            ->selectRaw('COALESCE(lv.quantity, 0) as quantity')
            ->leftJoinSub($levels, 'lv', 'lv.product_id', '=', 'products.id')
            ->with('category:id,name')
            ->where('products.track_stock', true)
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('products.name', 'like', "%{$s}%")->orWhere('products.sku', 'like', "%{$s}%")->orWhere('products.barcode', $s)))
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('products.category_id', $id))
            ->when(($filters['status'] ?? null) === 'low', fn ($q) => $q->whereRaw('COALESCE(lv.quantity, 0) <= products.reorder_level'))
            ->when(($filters['status'] ?? null) === 'out', fn ($q) => $q->whereRaw('COALESCE(lv.quantity, 0) <= 0'))
            ->when(($filters['status'] ?? null) === 'negative', fn ($q) => $q->whereRaw('COALESCE(lv.quantity, 0) < 0'))
            ->orderBy('products.name');

        return ListSort::apply($query, $filters, ['sku' => 'products.sku', 'name' => 'products.name', 'quantity' => 'quantity'])
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    /**
     * Product × location pairs at or below the product's reorder level (dashboard), emptiest first.
     * Per location, not summed: a store that ran out must show even if the warehouse is full.
     *
     * @return Collection<int, StockLevel>
     */
    public function lowStock(?int $locationId, int $limit = 8): Collection
    {
        return StockLevel::query()
            ->select('stock_levels.*')
            ->join('products', 'products.id', '=', 'stock_levels.product_id')
            ->join('locations', 'locations.id', '=', 'stock_levels.location_id')
            ->where('products.active', true)->where('products.track_stock', true)->where('locations.active', true)
            ->whereColumn('stock_levels.quantity', '<=', 'products.reorder_level')
            ->when($locationId, fn ($q) => $q->where('stock_levels.location_id', $locationId))
            ->with(['product:id,sku,name,unit,reorder_level', 'location:id,code,name'])
            ->orderBy('stock_levels.quantity')->orderBy('products.name')
            ->limit($limit)
            ->get();
    }

    /**
     * Quantity per location for one product.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function levelsFor(Product $product): Collection
    {
        $levels = StockLevel::where('product_id', $product->id)->pluck('quantity', 'location_id');

        return Location::orderBy('name')->get()->map(fn (Location $l) => [
            'location' => ['id' => $l->id, 'code' => $l->code, 'name' => $l->name, 'type' => $l->type, 'active' => $l->active],
            'quantity' => (float) ($levels[$l->id] ?? 0),
        ])->filter(fn ($row) => $row['location']['active'] || $row['quantity'] != 0)->values();
    }

    /**
     * The ledger, newest first.
     *
     * @param  array<string, mixed>  $filters  product_id?, location_id?, type?, date_from?, date_to?
     */
    public function movements(array $filters): LengthAwarePaginator
    {
        return StockMovement::query()
            ->with(['product:id,sku,name,unit', 'location:id,code,name', 'document:id,number,type,reason,reference,supplier,note,user_id,location_id,to_location_id', 'document.user:id,name'])
            ->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))
            ->when($filters['location_id'] ?? null, fn ($q, $id) => $q->where('location_id', $id))
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->whereHas('document', fn ($d) => $d->where('type', $t)))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderByDesc('id')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    /**
     * @param  array<string, mixed>  $filters  type?, location_id?, search? (number / reference / supplier)
     * @return Builder<StockDocument>
     */
    public function documentsQuery(array $filters): Builder
    {
        return StockDocument::query()
            ->with(['location:id,code,name', 'toLocation:id,code,name', 'user:id,name'])
            ->withCount('movements')
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->where('type', $t))
            ->when($filters['location_id'] ?? null, fn ($q, $id) => $q->where(fn ($q) => $q->where('location_id', $id)->orWhere('to_location_id', $id)))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('number', 'like', "%{$s}%")->orWhere('reference', 'like', "%{$s}%")->orWhere('supplier', 'like', "%{$s}%")))
            ->orderByDesc('id');
    }

    public function document(StockDocument $document): StockDocument
    {
        return $document->load(['location:id,code,name', 'toLocation:id,code,name', 'user:id,name',
            'movements' => fn ($q) => $q->orderBy('id'), 'movements.product:id,sku,name,unit', 'movements.location:id,code,name']);
    }

    // ── writing ──────────────────────────────────────────────────

    /**
     * Goods in from a supplier.
     *
     * @param  array{location_id:int, supplier?:string, reference?:string, note?:string, lines: array<int, array{product_id:int, quantity:float|string, unit_cost?:int|null}>}  $data
     */
    public function receive(array $data, int $userId): StockDocument
    {
        return DB::transaction(function () use ($data, $userId) {
            $location = $this->activeLocation($data['location_id']);
            $doc = $this->newDocument('receipt', $location->id, $userId, $data);
            foreach ($data['lines'] as $line) {
                $product = $this->trackedProduct($line['product_id']);
                $cost = $line['unit_cost'] ?? null;
                $this->move($doc, $product->id, $location->id, (string) $line['quantity'], $cost);
                if ($cost !== null) {
                    $product->update(['cost' => $cost]);   // last cost becomes the product's cost
                }
            }

            return $this->document($doc);
        });
    }

    /**
     * Opening balances when a store starts using the system (same shape as a receipt, type `opening`).
     *
     * @param  array{location_id:int, note?:string, lines: array<int, array{product_id:int, quantity:float|string, unit_cost?:int|null}>}  $data
     */
    public function opening(array $data, int $userId): StockDocument
    {
        return DB::transaction(function () use ($data, $userId) {
            $location = $this->activeLocation($data['location_id']);
            $doc = $this->newDocument('opening', $location->id, $userId, $data);
            foreach ($data['lines'] as $line) {
                $product = $this->trackedProduct($line['product_id']);
                $this->move($doc, $product->id, $location->id, (string) $line['quantity'], $line['unit_cost'] ?? null);
            }

            return $this->document($doc);
        });
    }

    /**
     * A correction with a reason. quantity is signed (+ found, − damaged); refused if it would take
     * the location below zero (the person can check the count first).
     *
     * @param  array{location_id:int, reason:string, note?:string, lines: array<int, array{product_id:int, quantity:float|string}>}  $data
     */
    public function adjust(array $data, int $userId): StockDocument
    {
        return DB::transaction(function () use ($data, $userId) {
            $location = $this->activeLocation($data['location_id']);
            $doc = $this->newDocument('adjustment', $location->id, $userId, $data);
            foreach ($data['lines'] as $line) {
                $product = $this->trackedProduct($line['product_id']);
                $this->move($doc, $product->id, $location->id, (string) $line['quantity'], null, refuseNegative: $product->name);
            }

            return $this->document($doc);
        });
    }

    /**
     * Move stock between locations: one document, an out line at the source and an in line at the destination.
     *
     * @param  array{location_id:int, to_location_id:int, note?:string, lines: array<int, array{product_id:int, quantity:float|string}>}  $data
     */
    public function transfer(array $data, int $userId): StockDocument
    {
        if ((int) $data['location_id'] === (int) $data['to_location_id']) {
            throw new BusinessRuleException('Choose two different locations.');
        }

        return DB::transaction(function () use ($data, $userId) {
            $from = $this->activeLocation($data['location_id']);
            $to = $this->activeLocation($data['to_location_id']);
            $doc = $this->newDocument('transfer', $from->id, $userId, $data + ['to_location_id' => $to->id]);
            foreach ($data['lines'] as $line) {
                $product = $this->trackedProduct($line['product_id']);
                $qty = (string) $line['quantity'];
                $this->move($doc, $product->id, $from->id, bcmul($qty, '-1', 3), null, refuseNegative: $product->name);
                $this->move($doc, $product->id, $to->id, $qty, null);
            }

            return $this->document($doc);
        });
    }

    // ── internals ────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $data
     */
    private function newDocument(string $type, int $locationId, int $userId, array $data): StockDocument
    {
        $doc = StockDocument::create([
            'number' => 'TMP-'.bin2hex(random_bytes(8)),
            'type' => $type,
            'location_id' => $locationId,
            'to_location_id' => $data['to_location_id'] ?? null,
            'reason' => $data['reason'] ?? null,
            'supplier' => $data['supplier'] ?? null,
            'reference' => $data['reference'] ?? null,
            'note' => $data['note'] ?? null,
            'user_id' => $userId,
        ]);
        // numbered from the id: unique and gap-free per type is not needed for stock documents
        $doc->update(['number' => StockDocument::PREFIXES[$type].'-'.str_pad((string) $doc->id, 6, '0', STR_PAD_LEFT)]);

        return $doc;
    }

    private function move(StockDocument $doc, int $productId, int $locationId, string $quantity, ?int $unitCost, ?string $refuseNegative = null): void
    {
        if (bccomp($quantity, '0', 3) === 0) {
            throw new BusinessRuleException('A line has a zero quantity.');
        }

        StockLevel::firstOrCreate(['product_id' => $productId, 'location_id' => $locationId], ['quantity' => 0]);
        $level = StockLevel::where('product_id', $productId)->where('location_id', $locationId)->lockForUpdate()->first();
        $balance = bcadd((string) $level->quantity, $quantity, 3);

        if ($refuseNegative !== null && bccomp($balance, '0', 3) < 0) {
            throw new BusinessRuleException("Not enough {$refuseNegative} at this location: ".rtrim(rtrim((string) $level->quantity, '0'), '.').' on hand.');
        }

        $level->update(['quantity' => $balance]);
        StockMovement::create([
            'stock_document_id' => $doc->id,
            'product_id' => $productId,
            'location_id' => $locationId,
            'quantity' => $quantity,
            'balance_after' => $balance,
            'unit_cost' => $unitCost,
        ]);
    }

    private function activeLocation(int $id): Location
    {
        $location = Location::findOrFail($id);
        if (! $location->active) {
            throw new BusinessRuleException("{$location->name} is inactive.");
        }

        return $location;
    }

    private function trackedProduct(int $id): Product
    {
        $product = Product::findOrFail($id);
        if (! $product->track_stock) {
            throw new BusinessRuleException("{$product->name} does not track stock.");
        }

        return $product;
    }
}
