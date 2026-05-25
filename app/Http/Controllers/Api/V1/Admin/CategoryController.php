<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

        $categories = Category::query()
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->has('code')) {
            $request->merge([
                'code' => strtoupper($request->string('code')->toString()),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:50', Rule::unique('categories', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
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

            return response()->json([
                'success' => false,
                'message' => 'Server error',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => $category,
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        if ($request->has('code')) {
            $request->merge([
                'code' => strtoupper($request->string('code')->toString()),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('categories', 'code')->ignore($category->id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
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

            return response()->json([
                'success' => false,
                'message' => 'Server error',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category,
        ]);
    }

}
