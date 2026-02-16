<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use App\Mail\LoginAuditMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LoginAuditController extends Controller
{
    public function testSendEmail(Request $request)
    {
        $user = $request->user();
        if (!$this->isAdminUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado.'
            ], 403);
        }

        try {
            $audits = LoginAudit::query()
                ->with(['user:id,name,email'])
                ->orderByDesc('logged_in_at')
                ->limit(10)
                ->get();

            $recipientEmail = config('mail.audit_recipient', 'admin@consultoriawk.com');
            $auditCollection = $audits;

            // Enviar IMEDIATAMENTE para teste (não na fila)
            Mail::to($recipientEmail)->send(
                new LoginAuditMail($auditCollection, $recipientEmail, $user->email)
            );

            return response()->json([
                'success' => true,
                'message' => 'Email de teste enviado com sucesso!',
                'recipient' => $recipientEmail,
                'records_sent' => count($audits),
                'triggered_by' => $user->email,
                'mail_driver' => config('mail.default'),
                'mail_host' => config('mail.mailers.' . config('mail.default') . '.host', 'log driver'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de teste', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar email: ' . $e->getMessage(),
                'mail_driver' => config('mail.default'),
                'debug_info' => [
                    'mail_host' => config('mail.mailers.' . config('mail.default') . '.host'),
                    'mail_port' => config('mail.mailers.' . config('mail.default') . '.port'),
                ]
            ], 500);
        }
    }

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

        // Enviar email com dados de acesso para admin
        $this->sendAuditEmail($audits->items(), $user->email);

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

    private function sendAuditEmail(array $audits, string $triggeredBy): void
    {
        try {
            $recipientEmail = config('mail.audit_recipient', 'admin@consultoriawk.com');
            
            if (!$recipientEmail) {
                \Log::warning('Email de auditoria não configurado');
                return;
            }

            // Limitar a 50 registros mais recentes por email
            $auditCollection = collect($audits)->take(50);

            // Enviar imediatamente (não enfileirar) para garantir entrega
            Mail::to($recipientEmail)->send(
                new LoginAuditMail($auditCollection, $recipientEmail, $triggeredBy)
            );

            \Log::info('Email de auditoria enviado com sucesso', [
                'recipient' => $recipientEmail,
                'records' => count($auditCollection),
                'triggered_by' => $triggeredBy
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de auditoria de login', [
                'error' => $e->getMessage(),
                'triggered_by' => $triggeredBy,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
