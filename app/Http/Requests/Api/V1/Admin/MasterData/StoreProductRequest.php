<?php

namespace App\Http\Requests\Api\V1\Admin\MasterData;

use Illuminate\Validation\Rule;

class StoreProductRequest extends MasterDataRequest
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
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'unit_id' => ['required', 'integer', Rule::exists('units', 'id')],
            'type' => ['required', 'string', Rule::in(['raw_material', 'finished_good', 'semi_finished', 'packaging'])],
            'min_stock' => ['sometimes', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
