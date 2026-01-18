<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get notifications for current user
     */
    public function getNotifications(Request $request)
    {
        $limit = $request->get('limit', 10);
        $notifications = $this->notificationService->getLatestNotificationsForUser(null, $limit);
        $unreadCount = $this->notificationService->getUnreadCountForUser();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $success = $this->notificationService->markAsRead($id);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification marked as read' : 'Notification not found'
        ]);
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllAsRead(Request $request)
    {
        $this->notificationService->markAllAsReadForUser();
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(Request $request)
    {
        $count = $this->notificationService->getUnreadCountForUser();
        
        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }
}