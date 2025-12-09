<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 * Endpoints para autenticação de usuários com tokens Sanctum
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * Cria um novo usuário no sistema
     *
     * @bodyParam name string required Nome do usuário (max: 255)
     * @bodyParam email string required Email único (max: 255)
     * @bodyParam password string required Senha (min: 8)
     * @bodyParam role string Role do usuário: admin, seller, customer (default: customer)
     *
     * @response 201 {
     *   "success": true,
     *   "message": "User registered successfully",
     *   "data": {
     *     "id": "uuid",
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "role": "customer"
     *   },
     *   "token": "api-token"
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "message": "The given data was invalid",
     *   "errors": {
     *     "email": ["The email has already been taken"]
     *   }
     * }
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|in:admin,seller,customer',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assign role (default: customer)
            $role = $validated['role'] ?? 'customer';
            $user->assignRole($role);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role,
                ],
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user
     *
     * Autentica um usuário e retorna um token Sanctum
     *
     * @bodyParam email string required Email do usuário
     * @bodyParam password string required Senha do usuário
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Login successful",
     *   "data": {
     *     "id": "uuid",
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "roles": ["customer"]
     *   },
     *   "token": "api-token"
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "message": "Invalid credentials"
     * }
     */
    public function login(Request $request): JsonResponse
    {
        // Debug: try reading the raw input
        $rawInput = file_get_contents('php://input');
        
        \Log::debug('Login request', [
            'method' => $request->method(),
            'content-type' => $request->header('Content-Type'),
            'all' => $request->all(),
            'input' => $request->input(),
            'json' => $request->json()->all(),
            'raw_input' => $rawInput,
        ]);

        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke old tokens for security
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token,
        ], 200);
    }

    /**
     * Get current user
     *
     * Retorna informações do usuário autenticado
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": "uuid",
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "roles": ["customer"],
     *     "permissions": ["view_dashboard"]
     *   }
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthenticated"
     * }
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ], 200);
    }

    /**
     * Logout user
     *
     * Revoga todos os tokens do usuário autenticado
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Logged out successfully"
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Logout from all devices
     *
     * Revoga todos os tokens do usuário (logout em todos os dispositivos)
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Logged out from all devices"
     * }
     */
    public function logoutAll(Request $request): JsonResponse
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices',
        ], 200);
    }

    /**
     * Refresh token
     *
     * Gera um novo token mantendo a sessão ativa
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Token refreshed",
     *   "token": "new-api-token"
     * }
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        // Delete old token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed',
            'token' => $token,
        ], 200);
    }
}
