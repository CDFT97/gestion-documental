<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $updatedUser = $this->userRepository->updateProfile($user, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'user' => $updatedUser
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el perfil'
            ], 500);
        }
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validatedData = $request->validated();

            // Verificar contraseña actual
            if (!$this->userRepository->verifyCurrentPassword($user, $validatedData['current_password'])) {
                return response()->json([
                    'message' => 'Los datos proporcionados no son válidos',
                    'errors' => [
                        'current_password' => ['La contraseña actual no es correcta']
                    ]
                ], 422);
            }

            $this->userRepository->updatePassword($user, $validatedData['password']);

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar la contraseña'
            ], 500);
        }
    }
}
