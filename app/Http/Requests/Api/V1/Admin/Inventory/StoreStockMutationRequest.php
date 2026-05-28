<?php

namespace App\Http\Requests\Api\V1\Admin\Inventory;

use App\Http\Requests\Api\V1\Admin\MasterData\MasterDataRequest;
use Illuminate\Validation\Rule;

class StoreStockMutationRequest extends MasterDataRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'type' => ['required', 'string', Rule::in(['in', 'out', 'transfer', 'adjustment'])],
            'from_warehouse_id' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('type'), ['out', 'transfer'], true)),
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id'),
            ],
            'to_warehouse_id' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('type'), ['in', 'transfer'], true)),
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id'),
                'different:from_warehouse_id',
            ],
            'qty' => ['required', 'numeric', 'gt:0'],
            'reference_type' => ['nullable', 'string', 'max:100'],
            'reference_id' => ['nullable', 'integer', 'min:1'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('type') !== 'adjustment') {
                return;
            }

            if (! $this->filled('from_warehouse_id') && ! $this->filled('to_warehouse_id')) {
                $validator->errors()->add('warehouse_id', 'Adjustment requires from_warehouse_id or to_warehouse_id.');
            }

            if ($this->filled('from_warehouse_id') && $this->filled('to_warehouse_id')) {
                $validator->errors()->add('warehouse_id', 'Adjustment can only use one warehouse direction.');
            }
        });
    }
}
