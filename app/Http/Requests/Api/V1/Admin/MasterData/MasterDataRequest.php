<?php

namespace App\Http\Requests\Api\V1\Admin\MasterData;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class MasterDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }

    protected function uppercaseCode(string $field = 'code'): void
    {
        if (! $this->has($field)) {
            return;
        }

        $this->merge([
            $field => strtoupper($this->string($field)->toString()),
        ]);
    }
}
