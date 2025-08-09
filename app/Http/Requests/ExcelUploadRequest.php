<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ExcelUploadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'El archivo es obligatorio.',
            'file.mimes' => 'El archivo debe ser de tipo Excel (xlsx o xls).',
            'file.file' => 'El archivo debe ser un archivo válido.',
            'file.max' => 'El archivo debe ser menor a 10MB.',
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
