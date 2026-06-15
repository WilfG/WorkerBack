<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Channels\ExpoPushChannel;

class ClientWorkerAppliedNotification extends Notification
{
    use Queueable;

    private $applicationData;

    public function __construct($applicationData)
    {
        $this->applicationData = $applicationData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        $channels = ['mail'];
        
        // Send via Expo push if user has push token
        if ($notifiable->pushToken) {
            $channels[] = ExpoPushChannel::class;
        }
        
        return $channels;
    }

     /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nouvelle candidature pour: ' . $this->applicationData['job_title'])
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line('Un artisan a postulé pour votre travail.')
            ->line('**Travail:** ' . $this->applicationData['job_title'])
            ->line('**Artisan:** ' . $this->applicationData['worker_name'])
            ->line('**Profession:** ' . $this->applicationData['worker_profession'])
            ->line('**Note:** ' . ($this->applicationData['worker_rating'] ?? 'Aucune note encore'))
            ->action('Voir la candidature', url('/jobs/' . $this->applicationData['job_id'] . '/applications'))
            ->line('Consultez les détails du profil de l\'artisan et décidez si vous souhaitez l\'engager.')
            ->salutation('Cordialement,');
    }


    /**
     * Get the Expo push representation of the notification.
     */
    public function toExpoPush($notifiable)
    {
        return [
            'title' => 'Nouvelle candidature',
            'body' => "{$this->applicationData['worker_name']} a postulé pour: {$this->applicationData['job_title']}",
            'data' => [
                'type' => 'worker_applied',
                'job_id' => $this->applicationData['job_id'],
                'worker_name' => $this->applicationData['worker_name'],
                'job_title' => $this->applicationData['job_title'],
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
            'type' => 'worker_applied',
            'job_id' => $this->applicationData['job_id'],
            'job_title' => $this->applicationData['job_title'],
            'worker_name' => $this->applicationData['worker_name'],
            'worker_profession' => $this->applicationData['worker_profession'],
        ];
    }
}
