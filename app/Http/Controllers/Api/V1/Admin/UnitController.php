<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\MasterData\StoreUnitRequest;
use App\Http\Requests\Api\V1\Admin\MasterData\UpdateUnitRequest;
use App\Http\Resources\Api\V1\Admin\UnitResource;
use App\Models\Unit;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $sortableFields = ['id', 'code', 'name', 'is_active', 'created_at'];
        $sort = $request->query('sort', 'code');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, $sortableFields, true)) {
            $sort = 'id';
        }

        $search = trim((string) $request->query('search', $request->query('q', '')));

        $units = Unit::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        return ApiResponse::paginated(
            'Units retrieved successfully',
            UnitResource::collection($units),
        );
    }

    public function store(StoreUnitRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $payload = [
                'code' => $request->string('code')->toString(),
                'name' => $request->string('name')->toString(),
            ];

            if ($request->has('description')) {
                $payload['description'] = $request->input('description') === null
                    ? null
                    : (string) $request->input('description');
            }

            if ($request->has('is_active')) {
                $payload['is_active'] = (bool) $request->boolean('is_active');
            }

            $unit = Unit::create($payload);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Unit creation failed: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::error('Server error', status: 500);
        }

        return ApiResponse::success(
            'Unit created successfully',
            new UnitResource($unit),
            201,
        );
    }

    public function show(Unit $unit): JsonResponse
    {
        return ApiResponse::success(
            'Unit retrieved successfully',
            new UnitResource($unit),
        );
    }

    public function update(UpdateUnitRequest $request, Unit $unit): JsonResponse
    {
        DB::beginTransaction();

        try {
            $payload = [];

            if ($request->has('code')) {
                $payload['code'] = $request->string('code')->toString();
            }

            if ($request->has('name')) {
                $payload['name'] = $request->string('name')->toString();
            }

            if ($request->has('description')) {
                $payload['description'] = $request->input('description') === null
                    ? null
                    : (string) $request->input('description');
            }

            if ($request->has('is_active')) {
                $payload['is_active'] = (bool) $request->boolean('is_active');
            }

            $unit->update($payload);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Unit update failed: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::error('Server error', status: 500);
        }

        return ApiResponse::success(
            'Unit updated successfully',
            new UnitResource($unit->refresh()),
        );
    }

    public function destroy(Unit $unit): JsonResponse
    {
        DB::beginTransaction();

        try {
            $unit->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Unit deletion failed: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::error('Unit is still used by other records or Server error.', status: 500);
        }

        return ApiResponse::success('Unit deleted successfully');
    }
}
