<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\MasterData\StoreCategoryRequest;
use App\Http\Requests\Api\V1\Admin\MasterData\UpdateCategoryRequest;
use App\Http\Resources\Api\V1\Admin\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
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

        $categories = Category::query()
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
            'Categories retrieved successfully',
            CategoryResource::collection($categories),
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
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

            $category = Category::create($payload);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::error('Server error', status: 500);
        }

        return ApiResponse::success(
            'Category created successfully',
            new CategoryResource($category),
            201,
        );
    }

    public function show(Category $category): JsonResponse
    {
        return ApiResponse::success(
            'Category retrieved successfully',
            new CategoryResource($category),
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
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

            $category->update($payload);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::error('Server error', status: 500);
        }

        return ApiResponse::success(
            'Category updated successfully',
            new CategoryResource($category->refresh()),
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        DB::beginTransaction();

        try {
            $category->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::error('Category is still used by other records or Server error.', status: 500);
        }

        return ApiResponse::success('Category deleted successfully');
    }
}
