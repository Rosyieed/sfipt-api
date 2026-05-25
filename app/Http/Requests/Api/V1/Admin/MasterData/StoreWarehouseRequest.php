<?php

namespace App\Http\Requests\Api\V1\Admin\MasterData;

use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends MasterDataRequest
{
    protected function prepareForValidation(): void
    {
        $this->uppercaseCode();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('warehouses', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'string', Rule::in(['raw', 'wip', 'finished', 'general'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
