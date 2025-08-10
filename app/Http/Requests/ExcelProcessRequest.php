<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ExcelProcessRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file_path' => 'required|string',
            'original_name' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'file_path.required' => 'La ruta del archivo es obligatoria.',
            'file_path.string' => 'La ruta del archivo debe ser una cadena de texto válida.',
            'original_name.required' => 'El nombre original del archivo es obligatorio.',
            'original_name.string' => 'El nombre original del archivo debe ser una cadena de texto válida.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Error de validación',
            'errors' => $validator->errors(),
            'status' => false
        ], 422));
    }
}
