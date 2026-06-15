<?php

namespace App\Services;

use App\Models\Job;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class JobDeliveryService
{
    public function deliverJob(Job $job, User $worker): bool
    {
        return DB::transaction(function () use ($job, $worker) {
            // Mark as delivered
            $job->markAsDelivered();
            
            // Create notification
            Notification::create([
                'user_id' => $job->client_id,
                'type' => 'job_delivered',
                'title' => 'Travail livré',
                'message' => "Le travail \"{$job->title}\" a été livré par {$worker->name}.",
                'data' => [
                    'job_id' => $job->id,
                    'worker_id' => $worker->id,
                    'worker_name' => $worker->name
                ]
            ]);
            
            return true;
        });
    }
    
    public function validateJob(Job $job, User $client): bool
    {
        return DB::transaction(function () use ($job, $client) {
            // Mark as completed
            $job->markAsCompleted();
            
            // Create notification
            Notification::create([
                'user_id' => $job->worker_id,
                'type' => 'job_validated',
                'title' => 'Travail validé',
                'message' => "Votre travail \"{$job->title}\" a été validé par le client.",
                'data' => [
                    'job_id' => $job->id,
                    'client_id' => $client->id
                ]
            ]);
            
            return true;
        });
    }
}