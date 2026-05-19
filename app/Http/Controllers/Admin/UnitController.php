<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

        $units = Unit::query()
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Units retrieved successfully',
            'data' => $units,
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
            'code' => ['required', 'string', 'max:50', Rule::unique('units', 'code')],
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

            $unit = Unit::create($payload);

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
            'message' => 'Unit created successfully',
            'data' => $unit,
        ], 201);
    }

    public function show(Unit $unit): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Unit retrieved successfully',
            'data' => $unit,
        ]);
    }

    public function update(Request $request, Unit $unit): JsonResponse
    {
        if ($request->has('code')) {
            $request->merge([
                'code' => strtoupper($request->string('code')->toString()),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('units', 'code')->ignore($unit->id)],
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

            $unit->update($payload);

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
            'message' => 'Unit updated successfully',
            'data' => $unit,
        ]);
    }
}
