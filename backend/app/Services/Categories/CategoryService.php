<?php

namespace App\Services\Categories;

use App\Exceptions\BusinessRuleException;
use App\Models\Category;
use App\Support\ListSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// Categories' business logic. Throw App\Exceptions\BusinessRuleException('message') for a refused action (→ 422).
class CategoryService
{
    // the list's column-header sort keys
    public const SORTS = ['name', 'active', 'created_at'];

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->query($filters)->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Category>
     */
    public function query(array $filters): Builder
    {
        $query = Category::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"),
            ))
            ->when(isset($filters['active']), fn ($q) => $q->where('active', $filters['active']))
            ->withCount('products')
            ->orderBy('name');

        return ListSort::apply($query, $filters, array_combine(self::SORTS, self::SORTS));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        return DB::transaction(fn () => Category::create($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $category->update($data);

            return $category;
        });
    }

    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            throw new BusinessRuleException('Products use this category: move them or deactivate the category.');
        }

        $category->delete();
    }
}
