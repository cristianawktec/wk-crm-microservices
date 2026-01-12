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
     * Sends to noreply@consultoriawk.com (configured in MAIL_FROM_ADDRESS)
     */
    private static function sendEmail(Notification $notification): void
    {
        try {
            // Get the notification recipient user (for context only, we send to noreply)
            $user = User::find($notification->user_id);
            
            // Get the FROM address (should be noreply@consultoriawk.com)
            $fromEmail = config('mail.from.address') ?? env('MAIL_FROM_ADDRESS', 'noreply@consultoriawk.com');
            
            if (empty($fromEmail) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Notification email skipped: invalid FROM address', [
                    'from_email' => $fromEmail,
                    'notification_id' => $notification->id
                ]);
                return;
            }

            \Log::info('[NotificationService] Sending email', [
                'from' => $fromEmail,
                'user' => $user?->name ?? 'Unknown',
                'user_id' => $notification->user_id,
                'title' => $notification->title,
                'type' => $notification->type,
            ]);

            $title = $notification->title ?? 'Notificação WK CRM';
            $body = $notification->message ?? '';
            $actionUrl = $notification->action_url ?? null;
            $createdAt = $notification->created_at ? $notification->created_at->toDateTimeString() : now()->toDateTimeString();

            // Send to the user's email address
            $recipientEmail = $user->email ?? null;
            
            if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Cannot send email: user has no valid email address', [
                    'user_id' => $notification->user_id,
                    'notification_id' => $notification->id
                ]);
                return;
            }

            $mail = Mail::to($recipientEmail);

            $mail->send(new \App\Mail\NotificationMail($title, $body, $actionUrl, $createdAt));
            
            \Log::info('[NotificationService] Email sent successfully', [
                'from' => $fromEmail,
                'user_id' => $notification->user_id,
                'notification_id' => $notification->id
            ]);
        } catch (\Swift_TransportException $e) {
            // SMTP/Transport errors (mailbox not found, connection issues, etc)
            Log::warning('Email transport error', [
                'notification_id' => $notification->id,
                'user_id' => $notification->user_id ?? null,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send email notification', [
                'notification_id' => $notification->id,
                'user_id' => $notification->user_id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
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
        try {
            $begin = microtime(true);
            
            // Collect all users to notify
            $notifyIds = [];
            
            // 1. Notify the creator (customer who created the opportunity)
            if ($createdBy) {
                $notifyIds[] = $createdBy->id;
                \Log::info('[NotificationService] Adding creator to notification', [
                    'creator_id' => $createdBy->id,
                    'creator_name' => $createdBy->name
                ]);
            }
            
            // 2. Notify managers and admins
            $qStart = microtime(true);
            $managerIds = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'manager']);
            })->limit(50)->pluck('id')->toArray();
            
            // Merge and remove duplicates
            $notifyIds = array_unique(array_merge($notifyIds, $managerIds));
            
            \Log::info('[NotificationService] Recipients fetched', [
                'total_count' => count($notifyIds),
                'managers_count' => count($managerIds),
                'creator_included' => (bool)$createdBy,
                'ms' => (int)((microtime(true) - $qStart) * 1000)
            ]);

            if (empty($notifyIds)) {
                Log::warning('No users found for opportunity notification');
                return;
            }

            $data = [
                'type' => 'opportunity_created',
                'title' => '🎯 Nova Oportunidade',
                'message' => "Oportunidade \"{$opportunity->title}\" foi criada. Valor: R\$ " . number_format($opportunity->value ?? 0, 2, ',', '.'),
                'action_url' => "/opportunities/{$opportunity->id}",
                'related_data' => [
                    'opportunity_id' => $opportunity->id,
                    'opportunity_title' => $opportunity->title,
                    'opportunity_value' => $opportunity->value ?? 0,
                    'seller_name' => $opportunity->seller?->name ?? 'Não atribuído'
                ]
            ];

            if ($createdBy) {
                $data['related_data']['created_by'] = $createdBy->name;
            }

            $nStart = microtime(true);
            static::notifyMany($notifyIds, $data);
            \Log::info('[NotificationService] notifyMany completed', [
                'recipients' => count($notifyIds),
                'ms' => (int)((microtime(true) - $nStart) * 1000),
                'total_ms' => (int)((microtime(true) - $begin) * 1000)
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed in opportunityCreated: ' . $e->getMessage());
        }
    }

    /**
     * Notify when opportunity status changes
     */
    public static function opportunityStatusChanged($opportunity, $oldStatus, $newStatus, $changedBy = null): void
    {
        try {
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
            
            // Remove duplicates and exclude who made the change
            $userIds = array_unique($userIds);
            if ($changedBy) {
                $userIds = array_diff($userIds, [$changedBy->id]);
            }

            if (empty($userIds)) {
                Log::info('No users to notify for status change (after excluding changer)');
                return;
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

            if ($changedBy) {
                $data['related_data']['changed_by'] = $changedBy->name;
            }

            static::notifyMany($userIds, $data);
            
            Log::info('[NotificationService] Status change notification sent', [
                'opportunity_id' => $opportunity->id,
                'recipients' => count($userIds),
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed in opportunityStatusChanged: ' . $e->getMessage());
        }
    }

    /**
     * Notify when opportunity value changes significantly
     */
    public static function opportunityValueChanged($opportunity, $oldValue, $newValue, $changedBy = null): void
    {
        try {
            if ($oldValue == 0 || $oldValue === null) {
                \Log::info('[NotificationService] Skipping value change notification (old value is zero/undefined)', [
                    'opportunity_id' => $opportunity->id,
                    'old' => $oldValue,
                    'new' => $newValue,
                ]);
                return;
            }

            $percentChange = (($newValue - $oldValue) / $oldValue) * 100;

            if (abs($percentChange) > 10) { // Only notify if change > 10%
                $managerIds = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['admin', 'manager']);
                })->pluck('id')->toArray();
                
                // Remove duplicates and exclude who made the change
                $managerIds = array_unique($managerIds);
                if ($changedBy) {
                    $managerIds = array_diff($managerIds, [$changedBy->id]);
                }

                if (empty($managerIds)) {
                    Log::info('No users to notify for value change (after excluding changer)');
                    return;
                }

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

                if ($changedBy) {
                    $data['related_data']['changed_by'] = $changedBy->name;
                }

                static::notifyMany($managerIds, $data);
                
                Log::info('[NotificationService] Value change notification sent', [
                    'opportunity_id' => $opportunity->id,
                    'recipients' => count($managerIds),
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'percent_change' => round($percentChange, 2)
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed in opportunityValueChanged: ' . $e->getMessage());
        }
    }
}
