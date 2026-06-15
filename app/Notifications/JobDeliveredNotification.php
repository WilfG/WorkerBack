<?php

namespace App\Notifications;

use App\Models\Job;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobDeliveredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Job $job,
        public User $worker
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Travail livré - ' . $this->job->title)
            ->greeting('Bonjour ' . $notifiable->name)
            ->line("Le travail \"{$this->job->title}\" a été livré par {$this->worker->name}.")
            ->line('Veuillez vérifier le travail effectué et valider ou rejeter la livraison.')
            ->action('Voir le travail', url("/jobs/{$this->job->id}"))
            ->line('Merci d\'utiliser notre plateforme!');
    }

    public function toArray($notifiable): array
    {
        return [
            'job_id' => $this->job->id,
            'job_title' => $this->job->title,
            'worker_id' => $this->worker->id,
            'worker_name' => $this->worker->name,
            'message' => "Le travail \"{$this->job->title}\" a été livré par {$this->worker->name}."
        ];
    }
}