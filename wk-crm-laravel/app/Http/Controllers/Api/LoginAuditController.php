<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use Illuminate\Http\Request;

class LoginAuditController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$this->isAdminUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado.'
            ], 403);
        }

        $perPage = (int) $request->query('per_page', 50);
        if ($perPage < 1) {
            $perPage = 50;
        }
        if ($perPage > 200) {
            $perPage = 200;
        }

        $query = LoginAudit::query()->with(['user:id,name,email']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('q')) {
            $search = (string) $request->query('q');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $audits = $query->orderByDesc('logged_in_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $audits,
        ]);
    }

    private function isAdminUser($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }

        $email = strtolower((string) ($user->email ?? ''));
        return in_array($email, ['admin@consultoriawk.com', 'admin-test@wkcrm.local'], true);
    }
}
