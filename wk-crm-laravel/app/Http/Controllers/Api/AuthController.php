<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
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
        // Accept both JSON and form-urlencoded data
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $passwordConfirmation = $request->input('password_confirmation');
        $role = $request->input('role', 'customer');

        // Manual validation
        if (empty($name)) {
            throw ValidationException::withMessages([
                'name' => ['The name field is required.'],
            ]);
        }
        if (empty($email)) {
            throw ValidationException::withMessages([
                'email' => ['The email field is required.'],
            ]);
        }
        if (empty($password)) {
            throw ValidationException::withMessages([
                'password' => ['The password field is required.'],
            ]);
        }
        if ($password !== $passwordConfirmation) {
            throw ValidationException::withMessages([
                'password' => ['The password confirmation does not match.'],
            ]);
        }

        // Check if email already exists
        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['The email has already been taken.'],
            ]);
        }
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            // Assign role (default: customer)
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
        // Accept both JSON and form-urlencoded data
        $email = $request->input('email');
        $password = $request->input('password');

        if (empty($email) || empty($password)) {
            throw ValidationException::withMessages([
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ]);
        }

        $user = User::where('email', $email)->first();

        if (!$user || !$this->isValidPassword($password, $user)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke old tokens for security
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->logLogin($request, $user);

        // Ensure customer record exists
        try {
            \App\Models\Customer::firstOrCreate(
                ['email' => $user->email],
                [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => '000000000'
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Error creating customer record: ' . $e->getMessage());
            // Continue even if customer creation fails
        }

        $roles = $user->getRoleNames();

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $roles->first(),
                'roles' => $roles,
            ],
            'token' => $token,
        ], 200);
    }

    private function logLogin(Request $request, User $user): void
    {
        try {
            $userAgent = (string) $request->header('User-Agent', '');
            $parsed = $this->parseUserAgent($userAgent);

            LoginAudit::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'platform' => $parsed['platform'],
                'browser' => $parsed['browser'],
                'device' => $parsed['device'],
                'route' => $request->path(),
                'method' => $request->method(),
                'accept_language' => (string) $request->header('Accept-Language', ''),
                'user_agent' => $userAgent,
                'logged_in_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to write login audit', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function parseUserAgent(string $userAgent): array
    {
        $ua = strtolower($userAgent);

        $platform = 'Unknown';
        if (str_contains($ua, 'android')) {
            $platform = 'Android';
        } elseif (str_contains($ua, 'iphone') || str_contains($ua, 'ipad')) {
            $platform = 'iOS';
        } elseif (str_contains($ua, 'windows')) {
            $platform = 'Windows';
        } elseif (str_contains($ua, 'mac')) {
            $platform = 'Mac';
        } elseif (str_contains($ua, 'linux')) {
            $platform = 'Linux';
        }

        $browser = 'Unknown';
        if (str_contains($ua, 'edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'opr/') || str_contains($ua, 'opera')) {
            $browser = 'Opera';
        } elseif (str_contains($ua, 'chrome/')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'firefox/')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'safari/') && !str_contains($ua, 'chrome/')) {
            $browser = 'Safari';
        }

        $device = 'Desktop';
        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) {
            $device = 'Tablet';
        } elseif (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            $device = 'Mobile';
        }

        return [
            'platform' => $platform,
            'browser' => $browser,
            'device' => $device,
        ];
    }

    private function isValidPassword(string $password, User $user): bool
    {
        $hash = (string) $user->password;

        if ($hash === '') {
            return false;
        }

        try {
            if (Hash::check($password, $hash)) {
                return true;
            }
        } catch (\RuntimeException $e) {
            \Log::warning('Non-bcrypt password hash detected', [
                'user_id' => $user->id,
            ]);
        }

        if ($this->matchesLegacyHash($password, $hash)) {
            $user->password = Hash::make($password);
            $user->save();
            return true;
        }

        return false;
    }

    private function matchesLegacyHash(string $password, string $hash): bool
    {
        if (preg_match('/^\$2[ayb]\$/', $hash) || str_starts_with($hash, '$argon2')) {
            return false;
        }

        if (preg_match('/^[a-f0-9]{32}$/i', $hash)) {
            return hash_equals(strtolower(md5($password)), strtolower($hash));
        }

        if (preg_match('/^[a-f0-9]{64}$/i', $hash)) {
            return hash_equals(strtolower(hash('sha256', $password)), strtolower($hash));
        }

        return hash_equals($hash, $password);
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
        try {
            $user = $request->user();
            
            if (!$user) {
                \Log::warning('Logout attempt without authenticated user');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $currentToken = $request->user()->currentAccessToken();
            
            if (!$currentToken) {
                \Log::warning('Logout attempt without valid token', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'No valid token found',
                ], 401);
            }

            \Log::info('User logout', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'token_id' => $currentToken->id,
            ]);

            // Revoke current token
            $currentToken->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('Logout error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage(),
            ], 500);
        }
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
