<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    protected function verificationUrl($notifiable)
    {
        $baseUrl = config('app.frontend_url');
        $url = parent::verificationUrl($notifiable);
        $params = parse_url($url, PHP_URL_QUERY);
        
        return "{$baseUrl}/verify-email?{$params}";
    }
}