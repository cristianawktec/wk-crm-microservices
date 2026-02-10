<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! $this->isValidPassword($credentials['password'], $user)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas são inválidas.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->json(['message' => 'Logout efetuado com sucesso.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
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
            Log::warning('Non-bcrypt password hash detected', [
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
}
