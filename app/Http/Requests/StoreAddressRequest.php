<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'street' => 'required|string|max:255|min:5',
            'number' => 'required|string|max:10',
            'city' => 'required|string|max:100',
            'cp' => 'required|string|max:8'
        ];
    }

    public function messages(): array
    {
        return [
            'street.required' => 'A rua é obrigatória',
            'street.min' => 'A rua deve ter pelo menos 5 caracteres',
            'number.required' => 'O número é obrigatório',
            'city.required' => 'A cidade é obrigatória',
            'cp.required' => 'O código postal é obrigatório'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422)
        );
    }
}
