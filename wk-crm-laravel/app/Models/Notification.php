<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Notification extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'action_url'
    ];

    protected $casts = [
        'data' => 'json',
        'read_at' => 'datetime'
    ];

    /**
     * Get the user that owns the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
        return $this;
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Get unread count for user
     */
    public static function unreadCount($userId): int
    {
        return static::where('user_id', $userId)->whereNull('read_at')->count();
    }

    /**
     * Get recent notifications for user
     */
    public static function getRecent($userId, $limit = 10)
    {
        return static::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
