<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\ExpoPushChannel;

class WorkerHiredNotification extends Notification
{
    use Queueable;

    private $hiredData;

    public function __construct($hiredData)
    {
        $this->hiredData = $hiredData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        $channels = [];
        
        // Send via Expo push if user has push token
        if ($notifiable->pushToken) {
            $channels[] = ExpoPushChannel::class;
        }
        
        return $channels;
    }

    /**
     * Get the Expo push representation of the notification.
     */
    public function toExpoPush($notifiable)
    {
        return [
            'title' => 'Félicitations! Vous êtes embauché 🎉',
            'body' => "Vous avez été sélectionné pour: {$this->hiredData['job_title']} par {$this->hiredData['client_name']}",
            'data' => [
                'type' => 'worker_hired',
                'job_title' => $this->hiredData['job_title'],
                'client_name' => $this->hiredData['client_name'],
            ],
            'sound' => 'default',
            'badge' => 1,
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'worker_hired',
            'job_title' => $this->hiredData['job_title'],
            'client_name' => $this->hiredData['client_name'],
        ];
    }
}
