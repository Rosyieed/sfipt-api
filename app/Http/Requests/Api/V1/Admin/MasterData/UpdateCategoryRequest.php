<?php

namespace App\Http\Requests\Api\V1\Admin\MasterData;

use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends MasterDataRequest
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
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('categories', 'code')->ignore($this->route('category'))],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
