<?php

namespace App\Http\Requests\Api\V1\Admin\MasterData;

use Illuminate\Validation\Rule;

class StoreCategoryRequest extends MasterDataRequest
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
            'code' => ['required', 'string', 'max:50', Rule::unique('categories', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
