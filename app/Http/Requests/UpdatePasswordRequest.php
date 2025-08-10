<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'current_password' => 'required',
      'password' => [
        'required',
        'confirmed',
        Password::min(8)
          ->letters()
          ->mixedCase()
          ->numbers()
      ],
    ];
  }

  public function messages()
  {
    return [
      'current_password.required' => 'La contraseña actual es obligatoria.',
      'password.required' => 'La nueva contraseña es obligatoria.',
      'password.confirmed' => 'La confirmación de contraseña no coincide.',
      'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
    ];
  }

  protected function failedValidation(Validator $validator)
  {
    throw new HttpResponseException(response()->json([
      'message' => 'Los datos proporcionados no son válidos',
      'errors' => $validator->errors(),
    ], 422));
  }
}
