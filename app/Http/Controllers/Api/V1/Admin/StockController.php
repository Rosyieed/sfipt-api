<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Admin\ProductResource;
use App\Http\Resources\Api\V1\Admin\StockResource;
use App\Models\Product;
use App\Models\Stock;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $sortableFields = ['id', 'product_id', 'warehouse_id', 'qty', 'created_at'];
        $sort = $request->query('sort', 'id');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, $sortableFields, true)) {
            $sort = 'id';
        }

        $search = trim((string) $request->query('search', $request->query('q', '')));

        $stocks = Stock::query()
            ->with(['product.category', 'product.unit', 'warehouse'])
            ->when($request->filled('product_id'), fn (Builder $query) => $query->where('product_id', $request->query('product_id')))
            ->when($request->filled('warehouse_id'), fn (Builder $query) => $query->where('warehouse_id', $request->query('warehouse_id')))
            ->when($request->boolean('low_stock'), function (Builder $query): void {
                $query->whereHas('product', function (Builder $query): void {
                    $query->whereColumn('stocks.qty', '<', 'products.min_stock');
                });
            })
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        return ApiResponse::paginated(
            'Stocks retrieved successfully',
            StockResource::collection($stocks),
        );
    }

    public function show(Stock $stock): JsonResponse
    {
        return ApiResponse::success(
            'Stock retrieved successfully',
            new StockResource($stock->load(['product.category', 'product.unit', 'warehouse'])),
        );
    }

    public function scan(string $barcode): JsonResponse
    {
        $product = Product::query()
            ->with(['category', 'unit', 'stocks.warehouse'])
            ->where('barcode', $barcode)
            ->first();

        if (! $product) {
            return ApiResponse::error('Product not found', status: 404);
        }

        return ApiResponse::success(
            'Product scanned successfully',
            new ProductResource($product),
        );
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query
                ->whereHas('product', function (Builder $query) use ($search): void {
                    $query
                        ->where('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                })
                ->orWhereHas('warehouse', function (Builder $query) use ($search): void {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
        });
    }
}
