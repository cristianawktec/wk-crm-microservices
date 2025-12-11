<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Create and send notification
     * 
     * @param array $data Array with keys: user_id, type, title, message, action_url, related_data
     * @return Notification
     */
    public static function notify(array $data): Notification
    {
        try {
            $notification = Notification::create([
                'user_id' => $data['user_id'],
                'type' => $data['type'] ?? 'default',
                'title' => $data['title'],
                'message' => $data['message'],
                'data' => $data['related_data'] ?? null,
                'action_url' => $data['action_url'] ?? null,
            ]);

            // Send email if configured
            if (config('app.notification_email', true)) {
                static::sendEmail($notification);
            }

            // Broadcast via SSE if enabled
            if (config('app.notification_sse', true)) {
                static::broadcastSSE($notification);
            }

            Log::info('Notification created', [
                'id' => $notification->id,
                'user_id' => $notification->user_id,
                'type' => $notification->type
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Error creating notification: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Notify multiple users
     */
    public static function notifyMany(array $userIds, array $data): array
    {
        $notifications = [];
        foreach ($userIds as $userId) {
            $notificationData = array_merge($data, ['user_id' => $userId]);
            $notifications[] = static::notify($notificationData);
        }
        return $notifications;
    }

    /**
     * Send email notification
     */
    private static function sendEmail(Notification $notification): void
    {
        try {
            $user = User::find($notification->user_id);
            if (!$user) return;

            // Mail::to($user->email)->queue(new NotificationMailMessage($notification));
            // For now, just log - implement Mailable class when ready
            Log::info('Email notification would be sent to: ' . $user->email, [
                'title' => $notification->title,
                'type' => $notification->type
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to send email notification: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast via SSE
     */
    private static function broadcastSSE(Notification $notification): void
    {
        try {
            // Store in Redis for SSE subscribers to pick up
            $sseKey = "notification:user:{$notification->user_id}";
            $message = json_encode([
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'timestamp' => $notification->created_at->toIso8601String(),
                'action_url' => $notification->action_url
            ]);

            // You can use Redis/Broadcast here
            // Cache::put($sseKey, $message, 3600);
            Log::info('SSE notification queued for user: ' . $notification->user_id);
        } catch (\Exception $e) {
            Log::warning('Failed to broadcast SSE notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify when opportunity is created
     */
    public static function opportunityCreated($opportunity, $createdBy = null): void
    {
        // Notify sales managers
        $managerIds = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'manager']);
        })->pluck('id')->toArray();

        $data = [
            'type' => 'opportunity_created',
            'title' => '🎯 Nova Oportunidade',
            'message' => "Oportunidade \"{$opportunity->title}\" foi criada. Valor: R\$ " . number_format($opportunity->value, 2, ',', '.'),
            'action_url' => "/opportunities/{$opportunity->id}",
            'related_data' => [
                'opportunity_id' => $opportunity->id,
                'opportunity_title' => $opportunity->title,
                'opportunity_value' => $opportunity->value,
                'seller_name' => $opportunity->seller?->name ?? 'Não atribuído'
            ]
        ];

        if ($createdBy) {
            $data['related_data']['created_by'] = $createdBy->name;
        }

        static::notifyMany($managerIds, $data);
    }

    /**
     * Notify when opportunity status changes
     */
    public static function opportunityStatusChanged($opportunity, $oldStatus, $newStatus): void
    {
        $statusLabel = match ($newStatus) {
            'open' => 'Aberta',
            'negotiation' => 'Em Negociação',
            'proposal' => 'Proposta Enviada',
            'won' => '✅ Ganha',
            'lost' => '❌ Perdida',
            default => $newStatus
        };

        // Notify managers and opportunity owner
        $userIds = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'manager']);
        })->pluck('id')->toArray();

        if ($opportunity->seller_id && !in_array($opportunity->seller_id, $userIds)) {
            $userIds[] = $opportunity->seller_id;
        }

        $data = [
            'type' => 'opportunity_status_changed',
            'title' => '📊 Status Atualizado',
            'message' => "Oportunidade \"{$opportunity->title}\" mudou para: {$statusLabel}",
            'action_url' => "/opportunities/{$opportunity->id}",
            'related_data' => [
                'opportunity_id' => $opportunity->id,
                'opportunity_title' => $opportunity->title,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'seller_name' => $opportunity->seller?->name ?? 'Não atribuído'
            ]
        ];

        static::notifyMany(array_unique($userIds), $data);
    }

    /**
     * Notify when opportunity value changes significantly
     */
    public static function opportunityValueChanged($opportunity, $oldValue, $newValue): void
    {
        $percentChange = (($newValue - $oldValue) / $oldValue) * 100;

        if (abs($percentChange) > 10) { // Only notify if change > 10%
            $managerIds = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'manager']);
            })->pluck('id')->toArray();

            $symbol = $percentChange > 0 ? '📈' : '📉';
            $data = [
                'type' => 'opportunity_value_changed',
                'title' => "{$symbol} Valor Alterado",
                'message' => "Oportunidade \"{$opportunity->title}\": R\$ " . number_format($oldValue, 2, ',', '.') . " → R\$ " . number_format($newValue, 2, ',', '.'),
                'action_url' => "/opportunities/{$opportunity->id}",
                'related_data' => [
                    'opportunity_id' => $opportunity->id,
                    'opportunity_title' => $opportunity->title,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'percent_change' => round($percentChange, 2)
                ]
            ];

            static::notifyMany($managerIds, $data);
        }
    }
}
