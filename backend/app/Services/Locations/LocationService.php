<?php

namespace App\Services\Locations;

use App\Exceptions\BusinessRuleException;
use App\Models\Location;
use App\Models\StockMovement;
use App\Support\ListSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// Locations' business logic. Throw App\Exceptions\BusinessRuleException('message') for a refused action (→ 422).
class LocationService
{
    // the list's column-header sort keys
    public const SORTS = ['code', 'name', 'type', 'active', 'created_at'];

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->query($filters)->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Location>
     */
    public function query(array $filters): Builder
    {
        $query = Location::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")->orWhere('address', 'like', "%{$search}%"),
            ))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when(isset($filters['active']), fn ($q) => $q->where('active', $filters['active']))
            ->orderBy('name');

        return ListSort::apply($query, $filters, array_combine(self::SORTS, self::SORTS));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Location
    {
        return DB::transaction(fn () => Location::create($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Location $location, array $data): Location
    {
        return DB::transaction(function () use ($location, $data) {
            $location->update($data);

            return $location;
        });
    }

    public function delete(Location $location): void
    {
        if (StockMovement::where('location_id', $location->id)->exists()) {
            throw new BusinessRuleException('This location has stock history: deactivate it instead of deleting it.');
        }

        $location->delete();
    }
}
