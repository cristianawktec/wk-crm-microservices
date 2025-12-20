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
            $user = $request->user();
            $limit = $request->query('limit', 20);

            $notifications = Notification::where('user_id', $user->id)
                ->orderByDesc('created_at')
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

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'total' => Notification::where('user_id', $user->id)->count(),
                'unread' => Notification::unreadCount($user->id)
            ]);
        } catch (\Exception $e) {
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

            // Verify notification belongs to user
            if ($notification->user_id !== $user->id) {
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

            if ($notification->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificação removida'
            ]);
        } catch (\Exception $e) {
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

            // Keep connection alive for 30 minutes
            $startTime = time();
            $timeout = 30 * 60; // 30 minutes
            // Track which notifications we've already sent to avoid duplicates
            $sentNotificationIds = [];

            while ((time() - $startTime) < $timeout) {
                \Log::debug('[NotificationController] Heartbeat loop iteration', [
                    'elapsed' => (time() - $startTime),
                    'user_id' => $user->id,
                ]);
                
                // Check for new unread notifications
                $unreadCount = \App\Models\Notification::unreadCount($user->id);

                // Send heartbeat with unread count every 10 seconds
                echo "data: {\"type\":\"heartbeat\",\"unread_count\":{$unreadCount}}\n\n";
                if (ob_get_level()) ob_flush();
                flush();

                // Emit all unread notifications that haven't been sent yet
                $allUnread = \App\Models\Notification::where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->orderBy('created_at')
                    ->get();

                $newNotifs = $allUnread->filter(function($n) use (&$sentNotificationIds) {
                    return !in_array($n->id, $sentNotificationIds);
                });

                if ($newNotifs->count() > 0) {
                    \Log::info('[NotificationController] New notifications to send', [
                        'count' => $newNotifs->count(),
                        'total_unread' => $allUnread->count(),
                    ]);
                }

                foreach ($newNotifs as $n) {
                    $sentNotificationIds[] = $n->id;
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

                sleep(10);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
