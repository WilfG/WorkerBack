<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Channels\ExpoPushChannel;

class WorkerNewJobNotification extends Notification
{
    use Queueable;

    private $jobData;

    public function __construct($jobData)
    {
        $this->jobData = $jobData;
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
            ->subject('Nouveau travail disponible - ' . $this->jobData['job_title'])
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line('Un nouveau travail correspond à votre profession.')
            ->line('**Titre:** ' . $this->jobData['job_title'])
            ->line('**Client:** ' . $this->jobData['client_name'])
            ->line('**Localisation:** ' . $this->jobData['location'])
            ->line('**Description:** ' . substr($this->jobData['job_description'], 0, 200) . (strlen($this->jobData['job_description']) > 200 ? '...' : ''))
            ->action('Voir le travail', url('/jobs/' . $this->jobData['job_id']))
            ->line('Postulez dès maintenant pour ne pas manquer cette opportunité!')
            ->salutation('Cordialement,');
    }


    /**
     * Get the Expo push representation of the notification.
     */
    public function toExpoPush($notifiable)
    {
        return [
            'title' => 'Nouveau travail disponible',
            'body' => "Nouveau travail: {$this->jobData['job_title']} par {$this->jobData['client_name']}",
            'data' => [
                'type' => 'new_job',
                'job_title' => $this->jobData['job_title'],
                'client_name' => $this->jobData['client_name'],
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
            'type' => 'new_job',
            'job_title' => $this->jobData['job_title'],
            'job_description' => $this->jobData['job_description'],
            'client_name' => $this->jobData['client_name'],
        ];
    }
}
