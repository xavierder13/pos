<?php

namespace App\Http\Controllers\API\Locations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\StoreLocationRequest;
use App\Http\Requests\Locations\UpdateLocationRequest;
use App\Http\Resources\Locations\LocationResource;
use App\Models\Location;
use App\Services\Locations\LocationService;
use App\Support\ListSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Thin controller: validate (Form Request) → Service → Resource.
class LocationController extends Controller
{
    public function __construct(private readonly LocationService $locations) {}

    // GET /locations?search=&type=store|warehouse&active=1|0&per_page=&page=&sort=&order=
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'active' => ['nullable', 'boolean'],
            'type' => ['nullable', 'in:store,warehouse'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ] + ListSort::rules(LocationService::SORTS));

        return LocationResource::collection($this->locations->paginate($filters));
    }

    public function show(Location $location): LocationResource
    {
        return new LocationResource($location);
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {
        return (new LocationResource($this->locations->create($request->validated())))->response()->setStatusCode(201);
    }

    public function update(UpdateLocationRequest $request, Location $location): LocationResource
    {
        return new LocationResource($this->locations->update($location, $request->validated()));
    }

    public function destroy(Location $location): JsonResponse
    {
        $this->locations->delete($location);

        return response()->json(['message' => 'Location deleted.']);
    }
}
