<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class DocumentUploadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:pdf|max:51200', // 50MB
            'name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'El archivo es obligatorio.',
            'file.file' => 'Debe seleccionar un archivo válido.',
            'file.mimes' => 'Solo se permiten archivos PDF.',
            'file.max' => 'El archivo no puede ser mayor a 50MB.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'category.string' => 'La categoría debe ser una cadena de texto.',
            'category.max' => 'La categoría no puede exceder 100 caracteres.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no puede exceder 1000 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Error de validación',
            'errors' => $validator->errors(),
        ], 422));
    }
}
