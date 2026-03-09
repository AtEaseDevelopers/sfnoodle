<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\DriverNotifications;

class OneSignalNotificationService
{
    protected $appId;
    protected $apiKey;
    protected $apiUrl = 'https://onesignal.com/api/v1/notifications';

    public function __construct()
    {
        $this->appId = '5081b7c1-4087-404f-b61f-d539660cbbb2';
        $this->apiKey = 'os_v2_app_kca3pqkaq5ae7nq72u4wmdf3wjvegjfsy5selle4semwxfvecn54r5bb3bwwufm67r7ojbuclzbifjl64ux5opxo2wwlsi2f6s5keni';
    }

    /**
     * Send notification to driver via OneSignal
     */
    public function sendToDriver($driverId, $title, $body, $data = [])
    {
        try {
            $driver = Driver::find($driverId);
            
            if (!$driver) {
                Log::warning("Driver {$driverId} not found");
                return false;
            }

            // Try OneSignal push if fcm_token exists
            if ($driver->fcm_token && $this->apiKey) {
                return $this->sendOneSignalPush($driver->fcm_token, $title, $body, $data);
            }

            return true; // Database notification saved

        } catch (\Exception $e) {
            Log::error('Driver notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to user (Inventory Admin) via OneSignal
     */
    public function sendToUser($userId, $title, $body, $data = [])
    {
        try {
            $user = User::find($userId);
            
            if (!$user) {
                Log::warning("User {$userId} not found");
                return false;
            }

            // Try OneSignal push if fcm_token exists
            if ($user->fcm_token && $this->apiKey) {
                return $this->sendOneSignalPush($user->fcm_token, $title, $body, $data);
            }

            Log::info("No FCM token for user {$user->id}, OneSignal push skipped");
            return true;

        } catch (\Exception $e) {
            Log::error('User notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification via OneSignal API
     */
    private function sendOneSignalPush($playerId, $title, $body, $data)
    {
        try {
            $payload = [
                'app_id' => $this->appId,
                'include_player_ids' => [$playerId],
                'headings' => ['en' => $title],
                'contents' => ['en' => $body],
                'data' => $data,
                'ios_badgeType' => 'Increase',
                'ios_badgeCount' => 1,
                'small_icon' => 'ic_stat_onesignal_default',
                'large_icon' => 'ic_launcher',
            ];

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Basic ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post($this->apiUrl, $payload);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info("OneSignal push sent successfully", [
                    'player_id' => substr($playerId, -10),
                    'title' => $title,
                    'recipients' => $responseData['recipients'] ?? 0
                ]);
                return true;
            } else {
                Log::error("OneSignal API error", [
                    'status' => $response->status(),
                    'error' => $responseData['errors'] ?? 'Unknown error',
                    'title' => $title
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('OneSignal request failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test OneSignal configuration
     */
    public function testConfiguration()
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'message' => 'OneSignal REST API Key not configured in .env'
            ];
        }

        return [
            'success' => true,
            'message' => 'OneSignal configured successfully',
            'app_id' => $this->appId,
            'api_key_preview' => substr($this->apiKey, 0, 10) . '...'
        ];
    }
}