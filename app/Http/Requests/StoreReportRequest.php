<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location' => 'required|string|max:255|min:5',
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
            'photo.mimes' => 'A imagem deve ser do tipo: jpeg, jpg, png',
            'photo.max' => 'A imagem não pode ter mais que 2MB',
            'category_id.required' => 'Pelo menos uma categoria é obrigatória',
            'category_id.array' => 'As categorias devem ser enviadas em array',
            'category_id.*.exists' => 'Uma das categorias selecionadas não existe'
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
