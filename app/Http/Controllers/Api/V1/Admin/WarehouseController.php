<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\MasterData\StoreWarehouseRequest;
use App\Http\Requests\Api\V1\Admin\MasterData\UpdateWarehouseRequest;
use App\Http\Resources\Api\V1\Admin\WarehouseResource;
use App\Models\Warehouse;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $sortableFields = ['id', 'code', 'name', 'location', 'type', 'is_active', 'created_at'];
        $sort = $request->query('sort', 'code');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, $sortableFields, true)) {
            $sort = 'id';
        }

        $search = trim((string) $request->query('search', $request->query('q', '')));

        $query = Warehouse::query()
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search));

        if (in_array($sort, ['code', 'name', 'location'], true)) {
            $warehouses = $query
                ->orderByRaw('LENGTH(' . $sort . ') ' . $direction)
                ->orderBy($sort, $direction)
                ->paginate($perPage);
        } else {
            $warehouses = $query
                ->orderBy($sort, $direction)
                ->paginate($perPage);
        }

        return ApiResponse::paginated(
            'Warehouses retrieved successfully',
            WarehouseResource::collection($warehouses),
        );
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function ($query) use ($search): void {
            $query
                ->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%");
        });
    }

    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $payload = [
                'code' => $request->string('code')->toString(),
                'name' => $request->string('name')->toString(),
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ];

            if ($request->has('location')) {
                $payload['location'] = $request->input('location') === null
                    ? null
                    : (string) $request->input('location');
            }

            if ($request->has('type')) {
                $payload['type'] = $request->string('type')->toString();
            }

            if ($request->has('is_active')) {
                $payload['is_active'] = (bool) $request->boolean('is_active');
            }

            $warehouse = Warehouse::create($payload);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::error('Server error', status: 500);
        }

        return ApiResponse::success(
            'Warehouse created successfully',
            new WarehouseResource($warehouse),
            201,
        );
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        return ApiResponse::success(
            'Warehouse retrieved successfully',
            new WarehouseResource($warehouse),
        );
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): JsonResponse
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

            if ($request->has('location')) {
                $payload['location'] = $request->input('location') === null
                    ? null
                    : (string) $request->input('location');
            }

            if ($request->has('type')) {
                $payload['type'] = $request->string('type')->toString();
            }

            if ($request->has('is_active')) {
                $payload['is_active'] = (bool) $request->boolean('is_active');
            }

            $payload['updated_by'] = $request->user()?->id;

            $warehouse->update($payload);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::error('Server error', status: 500);
        }

        return ApiResponse::success(
            'Warehouse updated successfully',
            new WarehouseResource($warehouse->refresh()),
        );
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        DB::beginTransaction();

        try {
            $warehouse->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::error('Warehouse is still used by other records or Server error.', status: 500);
        }

        return ApiResponse::success('Warehouse deleted successfully');
    }
}
