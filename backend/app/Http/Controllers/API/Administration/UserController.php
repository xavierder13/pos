<?php

namespace App\Http\Controllers\API\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\StoreUserRequest;
use App\Http\Requests\Administration\UpdateUserRequest;
use App\Http\Resources\Administration\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Services\Administration\UserService;
use App\Support\ListSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Thin controller: validate (Form Request) → Service → Resource.
class UserController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    // GET /users?search=&active=1|0&role_id=&per_page=&page=&sort=&order=
    public function index(Request $request): AnonymousResourceCollection
    {
        return UserResource::collection($this->users->paginate($this->filters($request)));
    }

    // GET /users/export?<the list's filters> → users.csv (the whole filtered list, in the list's order)
    public function export(Request $request): StreamedResponse
    {
        $query = $this->users->query($this->filters($request))->with('roles:id,name');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Roles', 'Status', 'Last sign-in', 'Created']);
            $query->chunk(500, function ($users) use ($out) {
                foreach ($users as $user) {
                    fputcsv($out, [
                        $user->name,
                        $user->email,
                        $user->roles->pluck('name')->sort()->implode(', '),
                        $user->active ? 'Active' : 'Inactive',
                        $user->last_login_at?->toDateTimeString(),
                        $user->created_at?->toDateTimeString(),
                    ]);
                }
            });
            fclose($out);
        }, 'users-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'active' => ['nullable', 'boolean'],
            'role_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ] + ListSort::rules(UserService::SORTS));
    }

    // the create / edit form's choices (needs user-create or user-edit, not role-list)
    public function formOptions(): JsonResponse
    {
        return response()->json(['roles' => Role::orderBy('name')->get(['id', 'name'])]);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load(UserService::WITH));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        return (new UserResource($this->users->create($request->validated())))->response()->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        return new UserResource($this->users->update($user, $request->validated(), $request->user()));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->users->delete($user, $request->user());

        return response()->json(['message' => 'User deleted.']);
    }
}
