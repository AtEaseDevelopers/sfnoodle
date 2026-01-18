<?php
namespace App\Services;

use App\Models\Notification;
use App\Models\Trip;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Carbon;

class NotificationService
{
    /**
     * Create trip end notification for admin
     */
    public function createTripEndNotification(Driver $driver, Trip $trip): void  
    {
        $title = "Trip Summary Generated Completed";
        $message = "Driver {$driver->name} has end a trip. Trip ID: T-{$trip->uuid}";

        $adminUsers = User::role('admin')->get();

        foreach ($adminUsers as $user) {
            Notification::create([
                'title' => $title,
                'message' => $message,
                'type' => 'success',
                'trip_id' => $trip->id,
                'driver_id' => $driver->id,
                'user_id' => $user->id,
                'is_read' => false
            ]);
        }

        // Flash a success message for the current user if they're an admin
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            session()->flash('notification', [
                'type' => 'success',
                'message' => $message,
            ]);
        }

    }

     public function getUnreadCountForUser($userId = null): int
    {
        $userId = $userId ?? auth()->id();
        return Notification::where('user_id', $userId)->unread()->count();
    }

    /**
     * Get latest notifications for current user
     */
    public function getLatestNotificationsForUser($userId = null, $limit = 10)
    {
        $userId = $userId ?? auth()->id();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        return Notification::with(['trip', 'driver'])
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$yesterday->startOfDay(), $today->endOfDay()])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();
            
        if ($notification) {
            $notification->update(['is_read' => true]);
            return true;
        }
        
        return false;
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllAsReadForUser($userId = null): void
    {
        $userId = $userId ?? auth()->id();
        Notification::where('user_id', $userId)
            ->unread()
            ->update(['is_read' => true]);
    }
}