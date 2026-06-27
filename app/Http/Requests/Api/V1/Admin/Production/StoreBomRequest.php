<?php

namespace App\Http\Requests\Api\V1\Admin\Production;

use App\Http\Requests\Api\V1\Admin\MasterData\MasterDataRequest;
use Illuminate\Validation\Rule;

class StoreBomRequest extends MasterDataRequest
{
    protected function prepareForValidation(): void
    {
        $this->uppercaseCode('code');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->whereIn('type', ['finished_good', 'semi_finished']);
                }),
            ],
            'code' => ['required', 'string', 'max:100', Rule::unique('boms', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'output_qty' => ['required', 'numeric', 'gt:0'],
            'is_default' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->whereIn('type', ['raw_material', 'semi_finished', 'packaging']);
                }),
                'distinct',
            ],
            'items.*.qty_needed' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_id' => ['nullable', 'integer', Rule::exists('units', 'id')],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'items.*.material_id' => 'material',
            'items.*.qty_needed' => 'qty needed',
            'items.*.unit_id' => 'unit',
        ];
    }
}
