<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\PersonalAccessToken;

class NotificationController extends Controller
{
    /**
     * Get user's recent notifications
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user(); // Agora sempre será autenticado
            $limit = (int) $request->query('limit', 20);
            $page = max(1, (int) $request->query('page', 1));
            $offset = ($page - 1) * $limit;

            // Admin vê tudo, usuários normais veem suas próprias
            $query = Notification::query()->orderByDesc('created_at');
            if (!$user || $user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }

            $notifications = $query
                ->skip($offset)
                ->limit($limit)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'action_url' => $notification->action_url,
                        'is_read' => $notification->isRead(),
                        'data' => $notification->data,
                        'created_at' => $notification->created_at->toIso8601String(),
                        'created_at_formatted' => $notification->created_at->diffForHumans()
                    ];
                });

            $total = ($user && $user->role === 'admin')
                ? Notification::count()
                : Notification::where('user_id', $user->id)->count();

            $unread = ($user && $user->role === 'admin')
                ? Notification::whereNull('read_at')->count()
                : Notification::where('user_id', $user->id)->unreadCount($user->id);

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'total' => $total,
                'unread' => $unread,
                'page' => $page,
                'limit' => $limit
            ]);
        } catch (\Exception $e) {
            \Log::error('[NotificationController@index] Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar notificações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread count
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => true,
                    'unread_count' => 0
                ]);
            }

            $count = Notification::unreadCount($user->id);

            return response()->json([
                'success' => true,
                'unread_count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read
     * 
     * @param Notification $notification
     * @param Request $request
     * @return JsonResponse
     */
    public function markAsRead(Notification $notification, Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $isAdmin = method_exists($user, 'roles')
                ? $user->roles()->where('name', 'admin')->exists()
                : ($user->role ?? null) === 'admin';

            // Verify notification belongs to user
            if ($notification->user_id !== $user->id && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notificação marcada como lida',
                'data' => [
                    'id' => $notification->id,
                    'is_read' => $notification->isRead()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Todas as notificações marcadas como lidas'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete notification
     * 
     * @param Notification $notification
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Notification $notification, Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            \Log::info('[NotificationController] Destroy called', [
                'notification_id' => $notification->id,
                'notification_user_id' => $notification->user_id,
                'user_id' => $user->id ?? null,
                'user_name' => $user->name ?? null,
            ]);

            $isAdmin = method_exists($user, 'roles')
                ? $user->roles()->where('name', 'admin')->exists()
                : ($user->role ?? null) === 'admin';

            \Log::info('[NotificationController] Admin check', [
                'is_admin' => $isAdmin,
                'user_role' => $user->role ?? null,
            ]);

            if ($notification->user_id !== $user->id && !$isAdmin) {
                \Log::warning('[NotificationController] Unauthorized delete', [
                    'notification_user_id' => $notification->user_id,
                    'user_id' => $user->id,
                    'is_admin' => $isAdmin,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            \Log::info('[NotificationController] Attempting to delete');
            $notification->delete();
            \Log::info('[NotificationController] Delete successful');

            return response()->json([
                'success' => true,
                'message' => 'Notificação removida'
            ]);
        } catch (\Exception $e) {
            \Log::error('[NotificationController] Delete error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Server-Sent Events stream for real-time notifications
     * 
     * @param Request $request
     * @return mixed
     */
    public function stream(Request $request)
    {
        // Tenta autenticar via sessão (cookie), bearer ou token na query string (?token=...)
        $user = $request->user();

        if (!$user) {
            $queryToken = $request->query('token');

            if ($queryToken) {
                \Log::info('[NotificationController] Stream token received', [
                    'token_length' => strlen($queryToken),
                    'token_first_20_chars' => substr($queryToken, 0, 20),
                ]);
                
                try {
                    $accessToken = PersonalAccessToken::findToken($queryToken);
                    \Log::info('[NotificationController] findToken result', [
                        'found' => !is_null($accessToken),
                        'has_tokenable' => !is_null($accessToken?->tokenable),
                    ]);
                    $user = $accessToken?->tokenable;
                    
                    if (!$user) {
                        \Log::warning('[NotificationController] Token not found in DB', [
                            'token_prefix' => substr($queryToken, 0, 10),
                            'token_exists' => !is_null($accessToken),
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('[NotificationController] findToken exception', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        if (!$user) {
            \Log::error('[NotificationController] Unauthorized SSE attempt', [
                'has_query_token' => $request->has('token'),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        \Log::info('[NotificationController] Starting SSE stream', [
            'user_id' => $user->id,
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->stream(function () use ($user) {
            \Log::info('[NotificationController] Inside stream callback', ['user_id' => $user->id]);
            
            // Disable output buffering
            if (ob_get_level()) ob_end_clean();
            
            // Send initial heartbeat
            echo "data: {\"type\":\"connected\",\"user_id\":\"{$user->id}\"}\n\n";
            \Log::info('[NotificationController] Sent connected event');
            if (ob_get_level()) ob_flush();
            flush();
            \Log::info('[NotificationController] Flushed connected event');

            // Short-lived stream to avoid exhausting PHP-FPM workers
            $unreadCount = \App\Models\Notification::unreadCount($user->id);
            echo "data: {\"type\":\"heartbeat\",\"unread_count\":{$unreadCount}}\n\n";
            if (ob_get_level()) ob_flush();
            flush();

            // Emit unread notifications once
            $allUnread = \App\Models\Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->orderBy('created_at')
                ->get();

            foreach ($allUnread as $n) {
                $payload = [
                    'type' => 'notification',
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'action_url' => $n->action_url,
                    'data' => $n->data,
                    'created_at' => $n->created_at->toIso8601String()
                ];
                echo 'data: '.json_encode($payload)."\n\n";
                if (ob_get_level()) ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
