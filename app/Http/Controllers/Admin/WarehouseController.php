<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $sortableFields = ['id', 'name', 'location', 'type', 'is_active', 'created_at'];
        $sort = $request->query('sort', 'name');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, $sortableFields, true)) {
            $sort = 'id';
        }

        $warehouses = in_array($sort, ['name', 'location'], true)
            ? $this->paginateNaturalSort($request, $sort, $direction, $perPage)
            : Warehouse::query()
                ->orderBy($sort, $direction)
                ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Warehouses retrieved successfully',
            'data' => $warehouses,
        ]);
    }

    /**
     * @param 'name'|'location' $sort
     * @param 'asc'|'desc' $direction
     */
    private function paginateNaturalSort(
        Request $request,
        string $sort,
        string $direction,
        int $perPage,
    ): LengthAwarePaginator {
        $page = max(1, (int) $request->query('page', 1));
        $warehouses = Warehouse::query()
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
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'type' => ['sometimes', 'string', Rule::in(['raw', 'wip', 'finished'])],
            'is_active' => ['sometimes', 'boolean'],
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
            $payload = [
                'name' => $request->string('name')->toString(),
                'location' => $request->string('location')->toString(),
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ];

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

            return response()->json([
                'success' => false,
                'message' => 'Server error',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Warehouse created successfully',
            'data' => $warehouse,
        ], 201);
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Warehouse retrieved successfully',
            'data' => $warehouse,
        ]);
    }

    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'location' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', Rule::in(['raw', 'wip', 'finished'])],
            'is_active' => ['sometimes', 'boolean'],
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

            if ($request->has('location')) {
                $payload['location'] = $request->string('location')->toString();
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

            return response()->json([
                'success' => false,
                'message' => 'Server error',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Warehouse updated successfully',
            'data' => $warehouse,
        ]);
    }

    public function destroy(Request $request, Warehouse $warehouse): JsonResponse
    {
        DB::beginTransaction();

        try {
            $warehouse->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Warehouse is still used by other records or Server error.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Warehouse deleted successfully',
        ]);
    }
}
