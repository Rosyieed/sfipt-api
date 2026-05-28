<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\Inventory\StoreStockMutationRequest;
use App\Http\Resources\Api\V1\Admin\StockMutationResource;
use App\Models\StockMutation;
use App\Services\StockService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockMutationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $sortableFields = ['id', 'mutation_number', 'type', 'qty', 'created_at'];
        $sort = $request->query('sort', 'created_at');
        $direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, $sortableFields, true)) {
            $sort = 'created_at';
        }

        $search = trim((string) $request->query('search', $request->query('q', '')));

        $mutations = StockMutation::query()
            ->with(['product.category', 'product.unit', 'fromWarehouse', 'toWarehouse'])
            ->when($request->filled('product_id'), fn (Builder $query) => $query->where('product_id', $request->query('product_id')))
            ->when($request->filled('type'), fn (Builder $query) => $query->where('type', $request->query('type')))
            ->when($request->filled('warehouse_id'), function (Builder $query) use ($request): void {
                $warehouseId = $request->query('warehouse_id');

                $query->where(function (Builder $query) use ($warehouseId): void {
                    $query
                        ->where('from_warehouse_id', $warehouseId)
                        ->orWhere('to_warehouse_id', $warehouseId);
                });
            })
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        return ApiResponse::paginated(
            'Stock mutations retrieved successfully',
            StockMutationResource::collection($mutations),
        );
    }

    public function store(StoreStockMutationRequest $request, StockService $stockService): JsonResponse
    {
        try {
            $mutation = $stockService->createMutation($this->payload($request));
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        }

        return ApiResponse::success(
            'Stock mutation created successfully',
            new StockMutationResource($mutation->load(['product.category', 'product.unit', 'fromWarehouse', 'toWarehouse'])),
            201,
        );
    }

    public function show(StockMutation $mutation): JsonResponse
    {
        return ApiResponse::success(
            'Stock mutation retrieved successfully',
            new StockMutationResource($mutation->load(['product.category', 'product.unit', 'fromWarehouse', 'toWarehouse'])),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(StoreStockMutationRequest $request): array
    {
        return [
            'product_id' => (int) $request->input('product_id'),
            'type' => (string) $request->input('type'),
            'from_warehouse_id' => $request->filled('from_warehouse_id') ? (int) $request->input('from_warehouse_id') : null,
            'to_warehouse_id' => $request->filled('to_warehouse_id') ? (int) $request->input('to_warehouse_id') : null,
            'qty' => number_format((float) $request->input('qty'), 4, '.', ''),
            'reference_type' => $request->input('reference_type') === null ? null : (string) $request->input('reference_type'),
            'reference_id' => $request->filled('reference_id') ? (int) $request->input('reference_id') : null,
            'reference_no' => $request->input('reference_no') === null ? null : (string) $request->input('reference_no'),
            'notes' => $request->input('notes') === null ? null : (string) $request->input('notes'),
            'created_by' => $request->user()?->id,
        ];
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('mutation_number', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")
                ->orWhereHas('product', function (Builder $query) use ($search): void {
                    $query
                        ->where('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
        });
    }
}
