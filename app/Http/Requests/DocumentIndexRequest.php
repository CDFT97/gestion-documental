<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class DocumentIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'per_page' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ];
    }

    public function messages()
    {
        return [
            'per_page.integer' => 'El número de elementos por página debe ser un número entero.',
            'per_page.min' => 'El número de elementos por página debe ser al menos 1.',
            'per_page.max' => 'El número de elementos por página no puede exceder 100.',
            'search.string' => 'El término de búsqueda debe ser una cadena de texto.',
            'search.max' => 'El término de búsqueda no puede exceder 255 caracteres.',
            'category.string' => 'La categoría debe ser una cadena de texto.',
            'category.max' => 'La categoría no puede exceder 100 caracteres.',
            'date_from.date' => 'La fecha de inicio debe ser una fecha válida.',
            'date_to.date' => 'La fecha de fin debe ser una fecha válida.',
            'date_to.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Error de validación',
            'errors' => $validator->errors(),
        ], 422));
    }

    /**
     * Obtener los filtros validados para la consulta
     */
    public function getFilters(): array
    {
        return array_filter([
            'search' => $this->input('search'),
            'category' => $this->input('category'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to')
        ]);
    }

    /**
     * Obtener el número de elementos por página
     */
    public function getPerPage(): int
    {
        return $this->input('per_page', 15);
    }
}
