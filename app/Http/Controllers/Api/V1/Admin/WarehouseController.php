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

        $warehouses = in_array($sort, ['code', 'name', 'location'], true)
            ? $this->paginateNaturalSort($request, $sort, $direction, $perPage, $search)
            : Warehouse::query()
                ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
                ->orderBy($sort, $direction)
                ->paginate($perPage);

        return ApiResponse::paginated(
            'Warehouses retrieved successfully',
            WarehouseResource::collection($warehouses),
        );
    }

    /**
     * @param  'code'|'name'|'location'  $sort
     * @param  'asc'|'desc'  $direction
     */
    private function paginateNaturalSort(
        Request $request,
        string $sort,
        string $direction,
        int $perPage,
        string $search,
    ): LengthAwarePaginator {
        $page = max(1, (int) $request->query('page', 1));
        $warehouses = Warehouse::query()
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->get()
            ->sort(function (Warehouse $first, Warehouse $second) use ($sort, $direction): int {
                $result = strnatcasecmp((string) $first->{$sort}, (string) $second->{$sort});

                if ($result === 0) {
                    $result = $first->id <=> $second->id;
                }

                return $direction === 'asc' ? $result : -$result;
            })
            ->values();

        return new LengthAwarePaginator(
            $warehouses->forPage($page, $perPage)->values(),
            $warehouses->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
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
