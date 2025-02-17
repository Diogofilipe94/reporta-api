<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location' => 'sometimes|string|max:255|min:5',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'category_id' => 'required|array',
            'category_id.*' => 'exists:categories,id'
        ];
    }

    public function messages(): array
    {
        return [
            'location.required' => 'A localização é obrigatória',
            'location.min' => 'A localização deve ter pelo menos 5 caracteres',
            'photo' => 'image|mimes:jpeg,png,jpg|max:20480',
            'category_id.required' => 'A categoria é obrigatória',
            'category_id.exists' => 'A categoria selecionada não existe'
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
