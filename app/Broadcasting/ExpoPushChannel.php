<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        $pushToken = $notifiable->pushToken;
        
        if (!$pushToken) {
            return;
        }

        $message = $notification->toExpoPush($notifiable);
        
        $this->sendPushNotification($pushToken->push_token, $message);
    }

    /**
     * Send push notification via Expo Push API
     */
    private function sendPushNotification($token, $message)
    {
        $url = 'https://exp.host/--/api/v2/push/send';
        
        $payload = [
            'to' => $token,
            'title' => $message['title'],
            'body' => $message['body'],
            'data' => $message['data'] ?? [],
            'sound' => $message['sound'] ?? 'default',
            'badge' => $message['badge'] ?? null,
        ];

        try {
            $response = Http::post($url, $payload);
            
            if ($response->successful()) {
                Log::info('Push notification sent successfully', ['token' => substr($token, 0, 20) . '...']);
            } else {
                Log::error('Failed to send push notification', [
                    'token' => substr($token, 0, 20) . '...',
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception sending push notification: ' . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...',
            ]);
        }
    }
}
