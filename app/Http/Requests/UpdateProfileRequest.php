<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    $user = $this->user();

    return [
      'name' => 'required|string|min:2|max:255',
      'email' => [
        'required',
        'email',
        'max:255',
        Rule::unique('users', 'email')->ignore($user->id)
      ],
    ];
  }

  /**
   * Get custom error messages for validator errors.
   */
  public function messages(): array
  {
    return [
      'name.required' => 'El nombre es obligatorio',
      'name.min' => 'El nombre debe tener al menos 2 caracteres',
      'name.max' => 'El nombre no puede tener más de 255 caracteres',
      'email.required' => 'El email es obligatorio',
      'email.email' => 'El email debe ser una dirección válida',
      'email.unique' => 'Este email ya está en uso',
      'email.max' => 'El email no puede tener más de 255 caracteres',
    ];
  }

  /**
   * Get custom attributes for validator errors.
   */
  public function attributes(): array
  {
    return [
      'name' => 'nombre',
      'email' => 'correo electrónico',
    ];
  }
}
