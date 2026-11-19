<?php

namespace App\Http\Resources\Administration;

use App\Models\Role;
use App\Support\Rbac;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Role */
class RoleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // the Administrator role can't be changed or deleted
            'read_only' => $this->name === Rbac::ADMINISTRATOR,
            'users_count' => $this->whenCounted('users'),
            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->map->only(['id', 'name'])->sortBy('name')->values()),
            'users' => $this->whenLoaded('users', fn () => $this->users->map->only(['id', 'name', 'email', 'active'])->values()),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
