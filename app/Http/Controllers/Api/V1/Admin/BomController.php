<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\Production\StoreBomRequest;
use App\Http\Requests\Api\V1\Admin\Production\UpdateBomRequest;
use App\Http\Resources\Api\V1\Admin\BomResource;
use App\Models\Bom;
use App\Models\Product;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $sortableFields = ['id', 'code', 'name', 'output_qty', 'is_default', 'is_active', 'created_at'];
        $sort = $request->query('sort', 'code');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, $sortableFields, true)) {
            $sort = 'id';
        }

        $search = trim((string) $request->query('search', $request->query('q', '')));

        $boms = Bom::query()
            ->with(['product', 'items.material', 'items.unit'])
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->when($request->filled('product_id'), fn (Builder $query) => $query->where('product_id', $request->query('product_id')))
            ->when($request->filled('is_active'), fn (Builder $query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        return ApiResponse::paginated(
            'BOMs retrieved successfully',
            BomResource::collection($boms),
        );
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function store(StoreBomRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $productId = $request->input('product_id');
            $isDefault = $request->boolean('is_default');

            if ($isDefault) {
                Bom::where('product_id', $productId)->where('is_default', true)->update(['is_default' => false]);
            }

            $bom = Bom::create([
                'product_id' => $productId,
                'code' => $request->input('code'),
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'output_qty' => $request->input('output_qty', 1.0000),
                'is_default' => $isDefault,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $items = $request->input('items', []);
            $bomItems = [];
            foreach ($items as $item) {
                $unitId = $item['unit_id'] ?? null;
                if ($unitId === null) {
                    $material = Product::find($item['material_id']);
                    $unitId = $material?->unit_id;
                }

                $bomItems[] = [
                    'material_id' => $item['material_id'],
                    'qty_needed' => $item['qty_needed'],
                    'unit_id' => $unitId,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $bom->items()->createMany($bomItems);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('BOM creation failed: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::error('Server error', status: 500);
        }

        return ApiResponse::success(
            'BOM created successfully',
            new BomResource($bom->load(['product', 'items.material', 'items.unit'])),
            201,
        );
    }

    public function show(Bom $bom): JsonResponse
    {
        return ApiResponse::success(
            'BOM retrieved successfully',
            new BomResource($bom->load(['product', 'items.material', 'items.unit'])),
        );
    }

    public function update(UpdateBomRequest $request, Bom $bom): JsonResponse
    {
        DB::beginTransaction();

        try {
            $productId = $request->input('product_id', $bom->product_id);
            
            if ($request->has('is_default')) {
                $isDefault = $request->boolean('is_default');
                if ($isDefault) {
                    Bom::where('product_id', $productId)->where('id', '!=', $bom->id)->where('is_default', true)->update(['is_default' => false]);
                }
            } else {
                $isDefault = $bom->is_default;
            }

            $bom->update([
                'product_id' => $productId,
                'code' => $request->input('code', $bom->code),
                'name' => $request->input('name', $bom->name),
                'description' => $request->input('description', $bom->description),
                'output_qty' => $request->input('output_qty', $bom->output_qty),
                'is_default' => $isDefault,
                'is_active' => $request->boolean('is_active', $bom->is_active),
            ]);

            if ($request->has('items')) {
                $bom->items()->delete();

                $items = $request->input('items', []);
                $bomItems = [];
                foreach ($items as $item) {
                    $unitId = $item['unit_id'] ?? null;
                    if ($unitId === null) {
                        $material = Product::find($item['material_id']);
                        $unitId = $material?->unit_id;
                    }

                    $bomItems[] = [
                        'material_id' => $item['material_id'],
                        'qty_needed' => $item['qty_needed'],
                        'unit_id' => $unitId,
                        'notes' => $item['notes'] ?? null,
                    ];
                }

                $bom->items()->createMany($bomItems);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('BOM update failed: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::error('Server error', status: 500);
        }

        return ApiResponse::success(
            'BOM updated successfully',
            new BomResource($bom->refresh()->load(['product', 'items.material', 'items.unit'])),
        );
    }

    public function destroy(Bom $bom): JsonResponse
    {
        DB::beginTransaction();

        try {
            $bom->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('BOM deletion failed: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::error('BOM is still used by other records or Server error.', status: 500);
        }

        return ApiResponse::success('BOM deleted successfully');
    }

    public function getByProduct(int $productId): JsonResponse
    {
        $product = Product::find($productId);
        if (! $product) {
            return ApiResponse::error('Product not found', status: 404);
        }

        $boms = Bom::where('product_id', $productId)
            ->with(['product', 'items.material', 'items.unit'])
            ->get();

        return ApiResponse::success(
            'BOMs retrieved successfully',
            BomResource::collection($boms),
        );
    }
}
