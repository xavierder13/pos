<?php

namespace App\Services\Products;

use App\Exceptions\BusinessRuleException;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\ListSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// Products' business logic. Throw App\Exceptions\BusinessRuleException('message') for a refused action (→ 422).
class ProductService
{
    // the list's column-header sort keys
    public const SORTS = ['sku', 'name', 'price', 'active', 'created_at'];

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->query($filters)->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Product>
     */
    public function query(array $filters): Builder
    {
        $query = Product::query()
            ->with('category:id,name')
            ->withSum('stockLevels as on_hand', 'quantity')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")->orWhere('barcode', $search),
            ))
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when(isset($filters['active']), fn ($q) => $q->where('active', $filters['active']))
            ->orderBy('name');

        return ListSort::apply($query, $filters, array_combine(self::SORTS, self::SORTS));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        return DB::transaction(fn () => $this->find(Product::create($data)));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update($data);

            return $this->find($product);
        });
    }

    public function find(Product $product): Product
    {
        return $product->load('category:id,name')->loadSum('stockLevels as on_hand', 'quantity');
    }

    public function delete(Product $product): void
    {
        if (StockMovement::where('product_id', $product->id)->exists()) {
            throw new BusinessRuleException('This product has stock history: deactivate it instead of deleting it.');
        }

        $product->delete();
    }
}
