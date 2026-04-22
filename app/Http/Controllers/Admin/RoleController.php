<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Roles retrieved successfully',
            'data' => $roles,
        ]);
    }

    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role retrieved successfully',
            'data' => $role,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
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

        $guardName = array_key_exists('sanctum', config('auth.guards', []))
            ? 'sanctum'
            : config('auth.defaults.guard');

        DB::beginTransaction();

        try {
            $role = Role::create([
                'name' => $request->string('name')->toString(),
                'guard_name' => $guardName,
            ]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->input('permissions', []));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Server error',
            ], 500);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role,
        ], 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
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

        if ($role->name === 'admin' && $request->has('name') && $request->string('name')->toString() !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'name' => ['The admin role name cannot be changed.'],
                ],
            ], 422);
        }

        DB::beginTransaction();

        try {
            if ($request->has('name')) {
                $role->update([
                    'name' => $request->string('name')->toString(),
                ]);
            }

            if ($request->has('permissions')) {
                $role->syncPermissions($request->input('permissions', []));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Server error',
            ], 500);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role,
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->name === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'role' => ['The admin role cannot be deleted.'],
                ],
            ], 422);
        }

        DB::beginTransaction();

        try {
            $role->delete();

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
            'message' => 'Role deleted successfully',
        ]);
    }
}
