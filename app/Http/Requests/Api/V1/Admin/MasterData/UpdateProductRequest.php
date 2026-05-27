<?php

namespace App\Http\Requests\Api\V1\Admin\MasterData;

use Illuminate\Validation\Rule;

class UpdateProductRequest extends MasterDataRequest
{
    protected function prepareForValidation(): void
    {
        $this->uppercaseCode('sku');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sku' => ['sometimes', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($this->route('product'))],
            'name' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', Rule::exists('categories', 'id')],
            'unit_id' => ['sometimes', 'integer', Rule::exists('units', 'id')],
            'type' => ['sometimes', 'string', Rule::in(['raw_material', 'finished_good', 'semi_finished', 'packaging'])],
            'min_stock' => ['sometimes', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
