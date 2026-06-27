<?php

namespace App\Http\Requests\Api\V1\Admin\Production;

use App\Http\Requests\Api\V1\Admin\MasterData\MasterDataRequest;
use Illuminate\Validation\Rule;

class UpdateBomRequest extends MasterDataRequest
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
                'sometimes',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->whereIn('type', ['finished_good', 'semi_finished']);
                }),
            ],
            'code' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('boms', 'code')->ignore($this->route('bom')),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'output_qty' => ['sometimes', 'numeric', 'gt:0'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.material_id' => [
                'required_with:items',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->whereIn('type', ['raw_material', 'semi_finished', 'packaging']);
                }),
                'distinct',
            ],
            'items.*.qty_needed' => ['required_with:items', 'numeric', 'gt:0'],
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
