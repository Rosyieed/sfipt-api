<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Permissions retrieved successfully',
            'data' => $permissions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
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
            $permission = Permission::firstOrCreate([
                'name' => $request->string('name')->toString(),
                'guard_name' => $guardName,
            ]);

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
            'message' => 'Permission created successfully',
            'data' => $permission,
        ], 201);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        if (in_array($permission->name, ['admin.access', 'app.access'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'permission' => ['This permission cannot be deleted.'],
                ],
            ], 422);
        }

        DB::beginTransaction();

        try {
            $permission->delete();

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
            'message' => 'Permission deleted successfully',
        ]);
    }
}
