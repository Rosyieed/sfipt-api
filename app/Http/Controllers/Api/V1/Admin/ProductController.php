<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\MasterData\StoreProductRequest;
use App\Http\Requests\Api\V1\Admin\MasterData\UpdateProductRequest;
use App\Http\Resources\Api\V1\Admin\ProductResource;
use App\Models\Product;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $sortableFields = ['id', 'sku', 'name', 'type', 'min_stock', 'is_active', 'created_at'];
        $sort = $request->query('sort', 'sku');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, $sortableFields, true)) {
            $sort = 'id';
        }

        $search = trim((string) $request->query('search', $request->query('q', '')));

        $products = Product::query()
            ->with(['category', 'unit'])
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->when($request->filled('type'), fn (Builder $query) => $query->where('type', $request->query('type')))
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->query('category_id')))
            ->when($request->filled('unit_id'), fn (Builder $query) => $query->where('unit_id', $request->query('unit_id')))
            ->when($request->filled('is_active'), fn (Builder $query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        return ApiResponse::paginated(
            'Products retrieved successfully',
            ProductResource::collection($products),
        );
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $product = Product::create($this->payload($request));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::error('Server error', status: 500);
        }

        return ApiResponse::success(
            'Product created successfully',
            new ProductResource($product->load(['category', 'unit'])),
            201,
        );
    }

    public function show(Product $product): JsonResponse
    {
        return ApiResponse::success(
            'Product retrieved successfully',
            new ProductResource($product->load(['category', 'unit'])),
        );
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        DB::beginTransaction();

        try {
            $product->update($this->payload($request, partial: true));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::error('Server error', status: 500);
        }

        return ApiResponse::success(
            'Product updated successfully',
            new ProductResource($product->refresh()->load(['category', 'unit'])),
        );
    }

    public function destroy(Product $product): JsonResponse
    {
        DB::beginTransaction();

        try {
            $product->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::error('Product is still used by other records or Server error.', status: 500);
        }

        return ApiResponse::success('Product deleted successfully');
    }

    public function findByBarcode(string $barcode): JsonResponse
    {
        $product = Product::query()
            ->with(['category', 'unit'])
            ->where('barcode', $barcode)
            ->first();

        if (! $product) {
            return ApiResponse::error('Product not found', status: 404);
        }

        return ApiResponse::success(
            'Product retrieved successfully',
            new ProductResource($product),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request, bool $partial = false): array
    {
        $fields = [
            'sku',
            'barcode',
            'name',
            'category_id',
            'unit_id',
            'type',
            'min_stock',
            'description',
            'is_active',
        ];

        $payload = [];

        foreach ($fields as $field) {
            if ($partial && ! $request->has($field)) {
                continue;
            }

            if (! $partial && ! $request->has($field)) {
                continue;
            }

            $payload[$field] = match ($field) {
                'sku', 'barcode', 'name', 'type', 'description' => $request->input($field) === null
                    ? null
                    : (string) $request->input($field),
                'category_id', 'unit_id' => (int) $request->input($field),
                'min_stock' => (string) $request->input($field),
                'is_active' => (bool) $request->boolean($field),
                default => $request->input($field),
            };
        }

        return $payload;
    }
}
