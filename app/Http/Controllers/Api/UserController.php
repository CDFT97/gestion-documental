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

    /**
     * @OA\Put(
     *     path="/user/profile",
     *     summary="Actualizar perfil de usuario",
     *     description="Actualiza la información del perfil del usuario autenticado (nombre y email)",
     *     operationId="updateUserProfile",
     *     tags={"User Profile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del perfil a actualizar",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "email"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 minLength=2,
     *                 maxLength=255,
     *                 description="Nombre completo del usuario",
     *                 example="Juan Carlos Pérez"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 maxLength=255,
     *                 description="Dirección de correo electrónico",
     *                 example="juan.carlos@example.com"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil actualizado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Perfil actualizado exitosamente"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Los datos proporcionados no son válidos"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"El nombre debe tener al menos 2 caracteres"}
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"Este email ya está en uso"}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al actualizar el perfil")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/user/password",
     *     summary="Cambiar contraseña",
     *     description="Actualiza la contraseña del usuario autenticado. Requiere la contraseña actual para verificación.",
     *     operationId="updateUserPassword",
     *     tags={"User Profile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos para cambiar la contraseña",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"current_password", "password", "password_confirmation"},
     *             @OA\Property(
     *                 property="current_password",
     *                 type="string",
     *                 format="password",
     *                 description="Contraseña actual del usuario",
     *                 example="mi_contraseña_actual"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 minLength=8,
     *                 description="Nueva contraseña (mínimo 8 caracteres, debe incluir mayúsculas, minúsculas y números)",
     *                 example="NuevaContraseña123"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 format="password",
     *                 description="Confirmación de la nueva contraseña",
     *                 example="NuevaContraseña123"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contraseña actualizada exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contraseña actualizada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación o contraseña actual incorrecta",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Los datos proporcionados no son válidos"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="current_password",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"La contraseña actual no es correcta"}
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"La contraseña debe tener al menos 8 caracteres"}
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"La confirmación de contraseña no coincide"}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al cambiar la contraseña")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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
