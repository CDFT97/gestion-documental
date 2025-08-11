<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Traits\HandlesPasswordResponses;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints para autenticación de usuarios"
 * )
 */
class AuthController extends Controller
{
    use HandlesPasswordResponses;

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Registrar un nuevo usuario",
     *     description="Crea una nueva cuenta de usuario y retorna un token de acceso",
     *     operationId="register",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del usuario a registrar",
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Juan Pérez"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="juan@example.com"),
     *             @OA\Property(property="password", type="string", minLength=8, example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", minLength=8, example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario registrado exitosamente"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string", example="1|abcdef123456789...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="object", 
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="Este email ya está registrado."))
     *             )
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request)
    {
        $user = $this->userRepository->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Iniciar sesión",
     *     description="Autentica un usuario y retorna un token de acceso",
     *     operationId="login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Credenciales de acceso",
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login exitoso"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string", example="2|abcdef123456789...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Credenciales incorrectas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="Las credenciales proporcionadas son incorrectas."))
     *             )
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        $user = $this->userRepository->where(['email' => $request->email], $type = 'first');

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Cerrar sesión",
     *     description="Invalida el token de acceso actual del usuario",
     *     operationId="logout",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout exitoso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout exitoso'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/user",
     *     summary="Obtener datos del usuario autenticado",
     *     description="Retorna los datos completos del usuario autenticado con estadísticas",
     *     operationId="getAuthenticatedUser",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Incluir relaciones adicionales (tables, documents)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"tables", "documents"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos del usuario",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function user(Request $request)
    {
        return response()->json(UserResource::make($request->user()));
    }

    /**
     * @OA\Post(
     *     path="/auth/forgot-password",
     *     summary="Solicitar restablecimiento de contraseña",
     *     description="Envía un enlace de restablecimiento de contraseña al email del usuario",
     *     operationId="forgotPassword",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Email del usuario",
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="juan@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enlace enviado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Enlace de restablecimiento enviado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="No encontramos un usuario con este email."))
     *             ),
     *             @OA\Property(property="status", type="boolean", example=false)
     *         )
     *     )
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $this->handlePasswordResponse($status, 'forgot');
    }

    /**
     * @OA\Post(
     *     path="/auth/reset-password",
     *     summary="Restablecer contraseña",
     *     description="Restablece la contraseña del usuario usando el token recibido por email",
     *     operationId="resetPassword",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos para restablecer contraseña",
     *         @OA\JsonContent(
     *             required={"token","email","password","password_confirmation"},
     *             @OA\Property(property="token", type="string", example="abcdef123456789..."),
     *             @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
     *             @OA\Property(property="password", type="string", minLength=8, example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", minLength=8, example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contraseña restablecida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Contraseña restablecida exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="token", type="array", @OA\Items(type="string", example="Token de reseteo requerido."))
     *             ),
     *             @OA\Property(property="status", type="boolean", example=false)
     *         )
     *     )
     * )
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $this->handlePasswordResponse($status, 'reset');
    }
}
