<?php

namespace App\Traits;

use Illuminate\Support\Facades\Password;

trait HandlesPasswordResponses
{
  /**
   * Manejar respuestas de Password facade de manera centralizada
   * 
   * @param string $status Estado devuelto por Password facade
   * @param string $operation Tipo de operación ('forgot' o 'reset')
   * @return \Illuminate\Http\JsonResponse
   */
  protected function handlePasswordResponse(string $status, string $operation = 'forgot')
  {
    $responses = [
      // Estados de forgot password
      Password::RESET_LINK_SENT => [
        'message' => 'Link de recuperación enviado a tu email',
        'status' => 'success',
        'code' => 200
      ],

      // Estados de reset password
      Password::PASSWORD_RESET => [
        'message' => 'Contraseña restablecida exitosamente',
        'status' => 'success',
        'code' => 200
      ],

      // Estados de error comunes
      Password::INVALID_USER => [
        'message' => $operation === 'forgot'
          ? 'No pudimos encontrar un usuario con ese email'
          : 'Usuario no encontrado',
        'status' => 'error',
        'code' => 400
      ],

      Password::INVALID_TOKEN => [
        'message' => 'Token inválido o expirado. Solicita un nuevo enlace de recuperación.',
        'status' => 'error',
        'code' => 400
      ],

      Password::RESET_THROTTLED => [
        'message' => 'Has solicitado demasiados enlaces de recuperación. Espera un momento antes de intentar nuevamente.',
        'status' => 'error',
        'code' => 429
      ],
    ];

    $defaultResponse = [
      'message' => $this->getDefaultErrorMessage($operation),
      'status' => 'error',
      'code' => 500
    ];

    $response = $responses[$status] ?? $defaultResponse;

    // Agregar información de debug en desarrollo
    if (config('app.debug')) {
      $response['debug'] = [
        'password_status' => $status,
        'operation' => $operation,
        'timestamp' => now()->toISOString(),
        'available_statuses' => array_keys($responses)
      ];
    }

    return response()->json([
      'message' => $response['message'],
      'status' => $response['status'],
      'debug' => $response['debug'] ?? null
    ], $response['code']);
  }

  /**
   * Obtener mensaje de error por defecto según la operación
   */
  private function getDefaultErrorMessage(string $operation): string
  {
    return match ($operation) {
      'forgot' => 'Error al enviar el enlace de recuperación. Intenta nuevamente más tarde.',
      'reset' => 'Error al restablecer la contraseña. Intenta nuevamente.',
      default => 'Ha ocurrido un error inesperado. Intenta nuevamente.'
    };
  }
}
