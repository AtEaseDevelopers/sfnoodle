<?php
namespace App\Services;

use App\Models\Notification;
use App\Models\Trip;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Carbon;

class NotificationService
{   
    protected $oneSignalService;

    public function __construct()
    {
        $this->oneSignalService = new OneSignalNotificationService();
    }

    /**
     * Create trip end notification for admin
     */
    public function createTripEndNotification(Driver $driver, Trip $trip): void  
    {
        $title = "Trip Summary Generated Completed";
        $message = "Driver {$driver->name} has end a trip. Trip ID: T-{$trip->uuid}";

        $adminUsers = User::role('admin')->get();
        $inventoryAdminUsers = User::role('Inventory Admin')->get();
        $adminUsers = $adminUsers->merge($inventoryAdminUsers);
    
        foreach ($adminUsers as $user) {
            Notification::create([
                'title' => $title,
                'message' => $message,
                'type' => 'success',
                'trip_id' => $trip->id,
                'inventory_count_id' => null,
                'inventory_request_id' => null,
                'driver_id' => $driver->id,
                'user_id' => $user->id,
                'is_read' => false
            ]);
        }

        // Flash a success message for the current user if they're an admin
        if (auth()->check() && auth()->user()->hasRole('admin') || auth()->check() && auth()->user()->hasRole('Inventory Admin')) {
            session()->flash('notification', [
                'type' => 'success',
                'message' => $message,
            ]);
        }

    }

    public function createStockRequestNotification(Driver $driver, $stockRequest): void
    {
        $title = "New Stock Request";
        $message = "Driver {$driver->name} has requested for stock.";

        $adminUsers = User::role('admin')->get();
        $inventoryAdminUsers = User::role('Inventory Admin')->get();
        $adminUsers = $adminUsers->merge($inventoryAdminUsers);

        foreach ($adminUsers as $user) {
            Notification::create([
                'title' => $title,
                'message' => $message,
                'type' => 'success',
                'trip_id' => $stockRequest->trip_id,
                'inventory_count_id' => null,
                'inventory_request_id' => $stockRequest->id,
                'driver_id' => $driver->id,
                'user_id' => $user->id,
                'is_read' => false
            ]);

            if ($user->fcm_token) {
                $this->oneSignalService->sendToUser(
                    $user->id,
                    $title,
                    $message,
                    [
                        'type' => 'stock_request',
                        'stock_request_id' => $stockRequest->id,
                        'driver_id' => $driver->id,
                        'driver_name' => $driver->name,
                        'trip_id' => $stockRequest->trip_id,
                        'action' => 'view_stock_request',
                    ]
                );
            }

        }

        // Flash a success message for the current user if they're an admin
        if (auth()->check() && auth()->user()->hasRole('admin') || auth()->check() && auth()->user()->hasRole('Inventory Admin')) {
            session()->flash('notification', [
                'type' => 'success',
                'message' => $message,
            ]);
        }

    }

    public function createStockCountNotification(Driver $driver, $stockCount): void
    {
        $title = "New Stock Count Request";
        $message = "Driver {$driver->name} has requested for stock count.";

        $adminUsers = User::role('admin')->get();
        $inventoryAdminUsers = User::role('Inventory Admin')->get(); 
        $adminUsers = $adminUsers->merge($inventoryAdminUsers);

        foreach ($adminUsers as $user) {
            Notification::create([
                'title' => $title,
                'message' => $message,
                'type' => 'success',
                'trip_id' => $stockCount->trip_id,
                'inventory_count_id' => $stockCount->id,
                'inventory_request_id' => null,
                'driver_id' => $driver->id,
                'user_id' => $user->id,
                'is_read' => false
            ]);

            if ($user->fcm_token) {
                $this->oneSignalService->sendToUser(
                    $user->id,
                    $title,
                    $message,
                    [
                        'type' => 'stock_count',
                        'stock_count_id' => $stockCount->id,
                        'driver_id' => $driver->id,
                        'driver_name' => $driver->name,
                        'trip_id' => $stockCount->trip_id,
                        'action' => 'view_stock_count',
                    ]
                );
            }

        }

        // Flash a success message for the current user if they're an admin
        if (auth()->check() && auth()->user()->hasRole('admin') || auth()->check() && auth()->user()->hasRole('Inventory Admin')) {
            session()->flash('notification', [
                'type' => 'success',
                'message' => $message,
            ]);
        }

    }

    public function getUnreadCountForUser($userId = null): int
    {
        $userId = $userId ?? auth()->id();
        $threeDaysAgo = Carbon::now()->subDays(3);
        return Notification::where('user_id', $userId)
            ->where('created_at', '>=', $threeDaysAgo)
            ->unread()
            ->count();
    }

    /**
     * Get latest notifications for current user
     */
    public function getLatestNotificationsForUser($userId = null)
    {
        $userId = $userId ?? auth()->id();
        $threeDaysAgo = Carbon::now()->subDays(3);
        return Notification::with(['trip', 'driver'])
            ->where('user_id', $userId)
            ->where('created_at', '>=', $threeDaysAgo)
            ->latest()
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