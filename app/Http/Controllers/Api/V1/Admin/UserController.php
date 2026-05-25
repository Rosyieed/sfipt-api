<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $sortableFields = ['id', 'name', 'email', 'created_at'];
        $sort = $request->query('sort', 'name');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, $sortableFields, true)) {
            $sort = 'id';
        }

        $users = in_array($sort, ['name', 'email'], true)
            ? $this->paginateNaturalSort($request, $sort, $direction, $perPage)
            : User::query()
                ->with(['roles.permissions', 'permissions'])
                ->orderBy($sort, $direction)
                ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => $users,
        ]);
    }

    /**
     * @param 'name'|'email' $sort
     * @param 'asc'|'desc' $direction
     */
    private function paginateNaturalSort(
        Request $request,
        string $sort,
        string $direction,
        int $perPage,
    ): LengthAwarePaginator {
        $page = max(1, (int) $request->query('page', 1));
        $users = User::query()
            ->with(['roles.permissions', 'permissions'])
            ->get()
            ->sort(function (User $first, User $second) use ($sort, $direction): int {
                $result = strnatcasecmp((string) $first->{$sort}, (string) $second->{$sort});

                if ($result === 0) {
                    $result = $first->id <=> $second->id;
                }

                return $direction === 'asc' ? $result : -$result;
            })
            ->values();

        return new LengthAwarePaginator(
            $users->forPage($page, $perPage)->values(),
            $users->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['sometimes', 'string', 'exists:roles,name'],
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'password' => $request->input('password'),
            ]);

            $roles = $request->input('roles');
            if (is_array($roles)) {
                $user->syncRoles($roles);
            } elseif ($request->filled('role')) {
                $user->syncRoles([$request->string('role')->toString()]);
            }

            if ($request->has('permissions')) {
                $user->syncPermissions($request->input('permissions', []));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Server error',
            ], 500);
        }

        $user->load(['roles.permissions', 'permissions']);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['roles.permissions', 'permissions']);

        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => $user,
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        // jika user id sama dengan user id request, return error
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot update your own account.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'role' => ['sometimes', 'string', 'exists:roles,name'],
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $payload = [];

            if ($request->has('name')) {
                $payload['name'] = $request->string('name')->toString();
            }

            if ($request->has('email')) {
                $payload['email'] = $request->string('email')->toString();
            }

            if ($request->filled('password')) {
                $payload['password'] = $request->input('password');
            }

            if (! empty($payload)) {
                $user->update($payload);
            }

            if ($request->has('roles') && is_array($request->input('roles'))) {
                $user->syncRoles($request->input('roles'));
            } elseif ($request->filled('role')) {
                $user->syncRoles([$request->string('role')->toString()]);
            }

            if ($request->has('permissions')) {
                $user->syncPermissions($request->input('permissions', []));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Server error',
            ], 500);
        }

        $user->load(['roles.permissions', 'permissions']);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()?->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'user' => ['You cannot delete your own account.'],
                ],
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Server error',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }
}
