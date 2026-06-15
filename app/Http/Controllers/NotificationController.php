<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Job;
use App\Models\Notification;
use App\Models\UserPushToken;
use App\Notifications\WorkerNewJobNotification;
use App\Notifications\ClientWorkerAppliedNotification;
use App\Notifications\WorkerHiredNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->update(['read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Notify workers when a new job is posted in their category
     */
    public function notifyWorkersNewJob(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer|exists:professions,id',
            'job_title' => 'required|string|max:255',
            'job_description' => 'required|string',
            'client_name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find all workers in this profession category
            $workers = User::where('user_type', 'ARTISAN')
                ->where('profession_id', $request->category_id)
                ->whereHas('pushToken') // Only users with push tokens
                ->with('pushToken')
                ->get();

            $notificationData = [
                'job_title' => $request->job_title,
                'job_description' => $request->job_description,
                'client_name' => $request->client_name
            ];

            foreach ($workers as $worker) {
                // Create notification record
                Notification::create([
                    'user_id' => $worker->id,
                    'title' => 'Nouveau travail disponible',
                    'message' => "Nouveau travail: {$request->job_title} par {$request->client_name}",
                    'data' => json_encode($notificationData),
                    'read' => false
                ]);

                // Send push notification
                $worker->notify(new WorkerNewJobNotification($notificationData));
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifications sent to ' . $workers->count() . ' workers'
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending worker notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notifications'
            ], 500);
        }
    }

    /**
     * Notify client when a worker applies to their job
     */
    public function notifyClientWorkerApplied(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|integer|exists:jobs,id',
            'worker_name' => 'required|string|max:255',
            'worker_profession' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find the job and its owner
            $job = Job::with('user.pushToken')->find($request->job_id);
            
            if (!$job || !$job->user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job or job owner not found'
                ], 404);
            }

            $client = $job->user;
            
            $notificationData = [
                'job_id' => $request->job_id,
                'job_title' => $job->title,
                'worker_name' => $request->worker_name,
                'worker_profession' => $request->worker_profession
            ];

            // Create notification record
            Notification::create([
                'user_id' => $client->id,
                'title' => 'Nouvelle candidature',
                'message' => "{$request->worker_name} ({$request->worker_profession}) a postulé pour: {$job->title}",
                'data' => json_encode($notificationData),
                'read' => false
            ]);

            // Send push notification if client has push token
            if ($client->pushToken) {
                $client->notify(new ClientWorkerAppliedNotification($notificationData));
            }

            return response()->json([
                'success' => true,
                'message' => 'Client notification sent successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending client notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification'
            ], 500);
        }
    }

    /**
     * Notify worker when they are hired
     */
    public function notifyWorkerHired(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'worker_id' => 'required|integer|exists:users,id',
            'job_title' => 'required|string|max:255',
            'client_name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find the worker
            $worker = User::with('pushToken')->find($request->worker_id);
            
            if (!$worker) {
                return response()->json([
                    'success' => false,
                    'message' => 'Worker not found'
                ], 404);
            }

            $notificationData = [
                'worker_id' => $request->worker_id,
                'job_title' => $request->job_title,
                'client_name' => $request->client_name
            ];

            // Create notification record
            Notification::create([
                'user_id' => $worker->id,
                'title' => 'Félicitations! Vous êtes embauché',
                'message' => "Vous avez été sélectionné pour: {$request->job_title} par {$request->client_name}",
                'data' => json_encode($notificationData),
                'read' => false
            ]);

            // Send push notification if worker has push token
            if ($worker->pushToken) {
                $worker->notify(new WorkerHiredNotification($notificationData));
            }

            return response()->json([
                'success' => true,
                'message' => 'Worker notification sent successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending worker hired notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for the authenticated user
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        Notification::where('user_id', $user->id)
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }
}
