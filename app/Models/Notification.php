<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'trip_id',
        'driver_id',
        'is_read',
        'user_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Get the trip associated with the notification
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get the driver associated with the notification
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($adminId = null): void
    {
        $this->update([
            'is_read' => true,
        ]);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope for notifications by type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}