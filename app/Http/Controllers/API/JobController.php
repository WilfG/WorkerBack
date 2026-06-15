<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Notifications\JobDeliveredNotification;
use App\Notifications\JobValidatedNotification;
use App\Notifications\JobRejectedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\WorkerNewJobNotification;
use App\Notifications\ClientWorkerAppliedNotification;




class JobController extends Controller
{
    public function getCurrentJobs()
    {
        Log::info('Fetching current jobs for worker ID: ' . auth()->id(), [
            'timestamp' => now(),
        ]);
        $jobs = Job::where('worker_id', auth()->id())
            ->where('status', 'in_progress')
            ->with(['client', 'profession'])
            ->orderBy('created_at', 'desc')
            ->get();

        Log::info('Current jobs fetched for worker ID: ' . auth()->id(), [
            'jobs_count' => $jobs->count(),
            'timestamp' => now(),
        ]);
        return response()->json($jobs);
    }

    public function getCompletedJobs()
    {
        Log::info('Fetching completed jobs for worker ID: ' . auth()->id(), [
            'timestamp' => now(),
        ]);
        $jobs = Job::where('worker_id', auth()->id())
            ->where('status', 'completed')
            ->with(['client', 'profession', 'rating'])
            ->orderBy('completed_at', 'desc')
            ->get();
        Log::info('Completed jobs fetched for worker ID: ' . auth()->id(), [
            'jobs_count' => $jobs->count(),
            'timestamp' => now(),
        ]);
        return response()->json($jobs);
    }

    public function getPostedJobs()
    {
        $jobs = Job::with(['applications.worker', 'profession'])
            ->where('client_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($jobs);
    }

    public function store(Request $request)
    {
        Log::info('Creating job request', [
            'user_id' => auth()->id(),
            'timestamp' => now(),
            'request_data' => $request->all()
        ]);

        Log::info('Received job creation request', [
            'data' => $request->all(),
            'files' => $request->files->all()
        ]);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'profession_id' => 'required|exists:professions,id',
           // 'budget' => 'nullable|numeric',
            'date_start' => 'nullable|date',
            'deadline' => 'nullable|date',
            'images' => 'nullable|array',
            'images.*' => 'required|file|mimes:jpeg,jpg,png|max:2048'
        ]);

        Log::info('images: ' . json_encode($request->images));
        if ($validator->fails()) {
            Log::error('Job creation validation failed', [
                'errors' => $validator->errors(),
                'files' => $request->files->all()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $job = Job::create([
                'title' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'profession_id' => $request->profession_id,
                'price' => $request->budget,
                'date_start' => $request->date_start,
                'deadline' => $request->deadline,
                'client_id' => auth()->id(),
                'status' => 'pending'
            ]);

            if ($request->hasFile('images')) {
                Log::info('Processing job images', [
                    'job_id' => $job->id,
                    'images_count' => count($request->file('images')),
                    'timestamp' => now(),
                ]);
                foreach ($request->file('images') as $image) {
                    $path = $image->store('job-images', 'public');
                    $job->images()->create([
                        'path' => $path
                    ]);
                }
            }
            
             // Send email notifications to subscribed workers of the profession
            $this->notifySubscribedWorkersByEmail($job);


            DB::commit();

            return response()->json([
                'message' => 'Job created successfully',
                'job' => $job->load('images')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job creation failed', [
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return response()->json(['message' => 'Failed to create job'], 500);
        }
    }

    public function available(Request $request)
    {
        // Only show jobs that are open for application (no worker assigned, status pending)
        $jobs = Job::whereNull('worker_id')
             ->with('client')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($jobs);
    }

    public function hire(Request $request, $jobId)
    {
        $job = Job::findOrFail($jobId);

        // Only allow if job is still open
        if ($job->worker_id) {
            return response()->json(['message' => 'Un artisan a déjà été embauché'], 409);
        }

        $request->validate([
            'worker_id' => 'required|exists:users,id',
        ]);

        $job->worker_id = $request->worker_id;
        $job->status = 'in_progress';
        $job->save();

        // Update application status
        $job->applications()->where('worker_id', $request->worker_id)->update(['status' => 'accepted']);
        $job->applications()->where('worker_id', '!=', $request->worker_id)->update(['status' => 'rejected']);

        return response()->json(['message' => 'Artisan embauché']);
    }

    public function cancelHire($id)
    {
        $job = Job::findOrFail($id);
        $job->worker_id = null;
         $job->status = 'pending';
        $job->applications()->update(['status' => 'pending']); // Reset all applications to
        $job->save();
    
        return response()->json(['success' => true]);
    }

    public function apply(Request $request, $jobId)
    {
        $job = Job::findOrFail($jobId);

        // Prevent duplicate applications
        if ($job->applications()->where('worker_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Déjà postulé'], 409);
        }

        $job->applications()->create([
            'worker_id' => auth()->id(),
            'status' => 'pending',
        ]);
         $this->notifyClientOfNewApplication($job, auth()->user());


        return response()->json(['message' => 'Candidature envoyée']);
    }

    public function applications($jobId)
    {
        $job = Job::with(['applications.worker'])->findOrFail($jobId);

        return response()->json([
            'applications' => $job->applications()->with('worker')->get()
        ]);
    }


    public function show($jobId)
    {
        $job = Job::with('client')->findOrFail($jobId);
        $hasApplied = false;
    
        if (auth()->user() && auth()->user()->role === 'worker') {
            $hasApplied = $job->applications()->where('worker_id', auth()->id())->exists();
        }
    
        return response()->json([
            'job' => $job,
            'hasApplied' => $hasApplied,
        ]);
    }
    
     /**
     * Worker delivers completed work
     */
       
    public function deliver(Job $job): JsonResponse
    {
        try {
            $user = Auth::user();
             Log::info('Worker delivering job', [
                'job_id' => $job->id,
                'worker_id' => $user->id,
                'job_worker_id' => $job->worker_id,
                'timestamp' => now(),
            ]);
            // Check if user is a worker
            if ($user->role !== 'worker') {
                return response()->json([
                    'message' => 'Seuls les travailleurs peuvent livrer des travaux'
                ], 403);
            }
    
            // Check if worker is assigned to this job
            if ((int)$job->worker_id !== $user->id) {
                return response()->json([
                    'message' => 'Vous n\'êtes pas assigné à ce travail'
                ], 403);
            }
    
            // Check if job can be delivered
            if (!$job->canBeDelivered()) {
                return response()->json([
                    'message' => 'Ce travail ne peut pas être livré dans son état actuel'
                ], 400);
            }
             Log::info('can deliver job', [
                'job_id' => $job->id,
                'worker_id' => $user->id,
                'timestamp' => now(),
            ]);
    
            DB::beginTransaction();
    
            // Mark job as delivered
            $job->markAsDelivered();
            Log::info('Job marked as delivered', [
                'job_id' => $job->id,
                'worker_id' => $user->id,
                'timestamp' => now(),
            ]);
            
            // Create notification for client
            $client = User::find($job->client_id);
            if (!$client) {
                Log::error("Client not found for notification", ['client_id' => $job->client_id]);
                // Continue with delivery but skip notification
                DB::commit(); // Don't forget to commit the job delivery
                return response()->json([
                    'message' => 'Job delivered successfully',
                    'warning' => 'Client notification could not be sent'
                ]);
            }
    
            // Clean and escape data for notification
            $cleanJobTitle = htmlspecialchars($job->title, ENT_QUOTES, 'UTF-8');
            $cleanWorkerName = htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8');
            
            $notificationData = [
                'user_id' => (int) $job->client_id,
                'type' => 'job_delivered',
                'title' => 'Travail livré',
                'message' => "Le travail \"{$cleanJobTitle}\" a été livré par {$cleanWorkerName}. Veuillez vérifier et valider la livraison.",
                'data' => [
                    'job_id' => (int) $job->id,
                    'worker_id' => (int) $user->id,
                    'worker_name' => htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8')
                ]
            ];
    
            Log::info("Creating client notification for job delivery", [
                'job_id' => $job->id,
                'client_id' => $job->client_id,
                'worker_id' => $user->id,
                'title' => $notificationData['title'],
                'message' => $notificationData['message']
            ]);
    
            $result = Notification::create($notificationData);
    
            Log::info('Notification created for job delivery', [
                'job_id' => $job->id,
                'client_id' => $job->client_id,
                'worker_id' => $user->id,
                'notification_id' => $result->id ?? null,
                'timestamp' => now(),
            ]);
    
            // Send email notification (optional)
            try {
                $job->client->notify(new JobDeliveredNotification($job, $user));
            } catch (\Exception $emailError) {
                Log::warning('Email notification failed but continuing', [
                    'error' => $emailError->getMessage(),
                    'job_id' => $job->id
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'message' => 'Travail livré avec succès',
                'job' => [
                    'id' => $job->id,
                    'title' => $job->title,
                    'status' => $job->status,
                    'delivered_at' => $job->delivered_at
                ]
            ]);
    
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error delivering job: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Erreur interne du serveur'
            ], 500);
        }
    }
    
     /**
     * Validate delivered work (Client accepts the work)
     * POST /api/jobs/{id}/validate
     */
    public function validateJob(Request $request, $id)
    {
        Log::info("Job validation attempt started", [
            'job_id' => $id,
            'client_id' => Auth::id(),
            'timestamp' => now()
        ]);

        DB::beginTransaction();
        
        try {
            $job = Job::findOrFail($id);
            $client = Auth::user();

            Log::info("Job loaded for validation", [
                'job_id' => $job->id,
                'job_status' => $job->status,
                'job_client_id' => $job->client_id,
                'current_client_id' => $client->id
            ]);

            // Verify client owns this job
            if ((int) $job->client_id !== $client->id) {
                Log::warning("Unauthorized validation attempt", [
                    'job_client_id' => $job->client_id,
                    'current_client_id' => $client->id
                ]);
                return response()->json(['error' => 'Unauthorized. You can only validate your own jobs.'], 403);
            }

            // Verify job is delivered and ready for validation
            if ($job->status !== 'delivered') {
                Log::warning("Invalid job status for validation", [
                    'current_status' => $job->status,
                    'required_status' => 'delivered'
                ]);
                return response()->json([
                    'error' => 'Job must be delivered before it can be validated',
                    'current_status' => $job->status
                ], 400);
            }

            // Update job status to completed
            $job->update([
                'status' => 'completed',
                'completed_at' => now(),
                'validation_notes' => $request->input('notes', null)
            ]);

            Log::info("Job status updated to completed", [
                'job_id' => $job->id,
                'completed_at' => $job->completed_at
            ]);

            // Create notification for worker (safely)
            $this->createWorkerNotification($job, $client, 'validated');

            DB::commit();

            Log::info("Job validation completed successfully", [
                'job_id' => $job->id,
                'client_id' => $client->id,
                'worker_id' => $job->worker_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job validated successfully. The work has been marked as completed.',
                'job' => $job->fresh(['worker', 'client', 'profession'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error("Job validation failed", [
                'job_id' => $id,
                'client_id' => Auth::id(),
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to validate job',
                'message' => 'An internal error occurred while validating the job.'
            ], 500);
        }
    }


     /**
     * Reject delivered work (Client requests revisions)
     * POST /api/jobs/{id}/reject
     */
    public function rejectJob(Request $request, $id)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:10|max:500',
            'specific_issues' => 'nullable|array',
            'specific_issues.*' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Log::info("Job rejection attempt started", [
            'job_id' => $id,
            'client_id' => Auth::id(),
            'reason_length' => strlen($request->reason),
            'timestamp' => now()
        ]);

        DB::beginTransaction();
        
        try {
            $job = Job::findOrFail($id);
            $client = Auth::user();

            Log::info("Job loaded for rejection", [
                'job_id' => $job->id,
                'job_status' => $job->status,
                'job_client_id' => $job->client_id,
                'current_client_id' => $client->id
            ]);

            // Verify client owns this job
            if ($job->client_id !== $client->id) {
                Log::warning("Unauthorized rejection attempt", [
                    'job_client_id' => $job->client_id,
                    'current_client_id' => $client->id
                ]);
                return response()->json(['error' => 'Unauthorized. You can only reject your own jobs.'], 403);
            }

            // Verify job is delivered and can be rejected
            if ($job->status !== 'delivered') {
                Log::warning("Invalid job status for rejection", [
                    'current_status' => $job->status,
                    'required_status' => 'delivered'
                ]);
                return response()->json([
                    'error' => 'Job must be delivered before it can be rejected',
                    'current_status' => $job->status
                ], 400);
            }

            // Prepare rejection data
            $rejectionData = [
                'reason' => $request->reason,
                'specific_issues' => $request->specific_issues ?? [],
                'rejected_at' => now(),
                'rejected_by' => $client->id
            ];

            // Update job status back to in_progress for rework
            $job->update([
                'status' => 'in_progress',
                'rejection_reason' => $request->reason,
                'rejection_details' => json_encode($rejectionData),
                'delivered_at' => null, // Clear previous delivery timestamp
                'rejection_count' => ($job->rejection_count ?? 0) + 1
            ]);

            Log::info("Job status updated for rejection", [
                'job_id' => $job->id,
                'rejection_count' => $job->rejection_count,
                'reason_preview' => substr($request->reason, 0, 100)
            ]);

            // Create notification for worker (safely)
            $this->createWorkerNotification($job, $client, 'rejected', $request->reason);

            DB::commit();

            Log::info("Job rejection completed successfully", [
                'job_id' => $job->id,
                'client_id' => $client->id,
                'worker_id' => $job->worker_id,
                'rejection_count' => $job->rejection_count
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job rejected successfully. The worker has been notified and can make revisions.',
                'job' => $job->fresh(['worker', 'client', 'profession']),
                'rejection_details' => $rejectionData
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error("Job rejection failed", [
                'job_id' => $id,
                'client_id' => Auth::id(),
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to reject job',
                'message' => 'An internal error occurred while rejecting the job.'
            ], 500);
        }
    }

    /**
     * Get job validation history
     * GET /api/jobs/{id}/validation-history
     */
    public function getValidationHistory($id)
    {
        try {
            $job = Job::findOrFail($id);
            $user = Auth::user();

            // Check if user has access to this job
            if ($job->client_id !== $user->id && $job->worker_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $history = [
                'job_id' => $job->id,
                'current_status' => $job->status,
                'delivered_at' => $job->delivered_at,
                'completed_at' => $job->completed_at,
                'rejection_count' => $job->rejection_count ?? 0,
                'validation_notes' => $job->validation_notes,
                'latest_rejection' => null
            ];

            // Parse rejection details if available
            if ($job->rejection_details) {
                $rejectionDetails = json_decode($job->rejection_details, true);
                if ($rejectionDetails) {
                    $history['latest_rejection'] = [
                        'reason' => $job->rejection_reason,
                        'specific_issues' => $rejectionDetails['specific_issues'] ?? [],
                        'rejected_at' => $rejectionDetails['rejected_at'] ?? null,
                        'rejected_by' => $rejectionDetails['rejected_by'] ?? null
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'validation_history' => $history
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to get validation history", [
                'job_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get validation history'
            ], 500);
        }
    }
    
    /**
     * Notify client when a worker applies for their job via email
     */
    private function notifyClientOfNewApplication($job, $worker)
    {
        try {
            Log::info('Starting to notify client of new application', [
                'job_id' => $job->id,
                'worker_id' => $worker->id,
                'timestamp' => now(),
            ]);

            // Load the client
            $job->load(['client', 'profession']);
            $client = $job->client;

            // Verify client exists
            if (!$client) {
                Log::error("Client not found for notification", [
                    'job_id' => $job->id,
                    'client_id' => $job->client_id
                ]);
                return;
            }

            // Prepare application data for notification
            $applicationData = [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'worker_name' => $worker->name,
                'worker_profession' => $worker->profession->name ?? 'Unknown Profession',
                'worker_rating' => $worker->rating ?? 0,
            ];

            Log::info('Sending email notification to client about new application', [
                'job_id' => $job->id,
                'worker_id' => $worker->id,
                'client_id' => $client->id,
                'client_email' => $client->email,
                'timestamp' => now(),
            ]);

            // Send email notification to client
            $client->notify(new ClientWorkerAppliedNotification($applicationData));

            Log::info('Email notification sent to client about new application', [
                'job_id' => $job->id,
                'worker_id' => $worker->id,
                'client_id' => $client->id,
                'timestamp' => now(),
            ]);

            // Create in-app notification for client
            try {
                $cleanJobTitle = htmlspecialchars($job->title, ENT_QUOTES, 'UTF-8');
                $cleanWorkerName = htmlspecialchars($worker->name, ENT_QUOTES, 'UTF-8');

                $notificationData = [
                    'user_id' => (int) $client->id,
                    'type' => 'worker_applied',
                    'title' => 'Nouvelle candidature',
                    'message' => "{$cleanWorkerName} a postulé pour le travail \"{$cleanJobTitle}\"",
                    'data' => [
                        'job_id' => (int) $job->id,
                        'worker_id' => (int) $worker->id,
                        'worker_name' => $cleanWorkerName,
                        'job_title' => $cleanJobTitle,
                        'action_type' => 'worker_applied'
                    ]
                ];

                Notification::create($notificationData);

                Log::info("In-app notification created for client about new application", [
                    'job_id' => $job->id,
                    'worker_id' => $worker->id,
                    'client_id' => $client->id
                ]);
            } catch (\Exception $e) {
                Log::warning("Failed to create in-app notification but email sent", [
                    'job_id' => $job->id,
                    'worker_id' => $worker->id,
                    'error' => $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify client of new application', [
                'job_id' => $job->id ?? 'unknown',
                'worker_id' => $worker->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw exception - notification failure shouldn't break application creation
        }
    }


    /**
     * Notify all subscribed workers in the profession via email
     */
    private function notifySubscribedWorkersByEmail($job)
    {
        try {
            Log::info('Starting to email notify subscribed workers', [
                'job_id' => $job->id,
                'profession_id' => $job->profession_id,
                'timestamp' => now(),
            ]);

            // Load the profession with workers
            $job->load(['profession', 'client']);

            // Get all workers in this profession who have active subscriptions
            $subscribedWorkers = User::where('profession_id', $job->profession_id)
                ->where('role', 'worker')
                ->where('is_subscribed', true)
             //   ->whereDate('subscription_end_date', '>=', now())
                ->get();

            Log::info('Found subscribed workers for email notification', [
                'job_id' => $job->id,
                'profession_id' => $job->profession_id,
                'subscribed_workers_count' => $subscribedWorkers->count(),
                'timestamp' => now(),
            ]);

            if ($subscribedWorkers->isEmpty()) {
                Log::info('No subscribed workers found for profession', [
                    'profession_id' => $job->profession_id,
                    'job_id' => $job->id
                ]);
                return;
            }

            // Prepare job data for notification
            $jobData = [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'job_description' => $job->description,
                'client_name' => $job->client->name ?? 'Unknown Client',
                'location' => $job->location,
            ];

            // Send notification to each subscribed worker
            foreach ($subscribedWorkers as $worker) {
                try {
                    Log::info('Sending email notification to worker', [
                        'job_id' => $job->id,
                        'worker_id' => $worker->id,
                        'worker_email' => $worker->email,
                        'timestamp' => now(),
                    ]);

                    // Send email notification
                    $worker->notify(new WorkerNewJobNotification($jobData));

                    Log::info('Email notification sent to worker', [
                        'job_id' => $job->id,
                        'worker_id' => $worker->id,
                        'timestamp' => now(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send email notification to worker', [
                        'job_id' => $job->id,
                        'worker_id' => $worker->id,
                        'worker_email' => $worker->email ?? 'unknown',
                        'error' => $e->getMessage(),
                        'timestamp' => now(),
                    ]);
                    // Continue notifying other workers even if this one fails
                    continue;
                }
            }

            Log::info('Completed email notification to subscribed workers', [
                'job_id' => $job->id,
                'profession_id' => $job->profession_id,
                'workers_notified' => $subscribedWorkers->count(),
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in notifySubscribedWorkersByEmail', [
                'job_id' => $job->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw exception - notification failure shouldn't break job creation
        }
    }



      /**
     * Helper method to safely create notifications for workers
     * This method handles the foreign key constraint issue you experienced earlier
     */
    private function createWorkerNotification($job, $client, $action, $reason = null)
    {
        try {
            
            Log::info("Creating worker notification for job", [
                'job_id' => $job->id,
                'worker_id' => $job->worker_id,
                'action' => $action,
                'client_id' => $client->id,
                'timestamp' => now()
            ]);
            // Check if worker exists first (prevents foreign key constraint errors)
            
            // Check if worker exists first (prevents foreign key constraint errors)
            $worker = User::find($job->worker_id);
            if (!$worker) {
                Log::info("Worker not found for notification", [
                    'job_id' => $job->id,
                    'worker_id' => $job->worker_id,
                    'action' => $action
                ]);
                return false;
            }
            
            // Prepare notification content based on action
            if ($action === 'validated') {
                $title = 'Travail validé';
                // Clean and escape the job title and client name
                $cleanJobTitle = htmlspecialchars($job->title, ENT_QUOTES, 'UTF-8');
                $cleanClientName = htmlspecialchars($client->name, ENT_QUOTES, 'UTF-8');
                
                $message = "Félicitations ! Le travail \"{$cleanJobTitle}\" a été validé par {$cleanClientName}. Vous avez terminé ce projet avec succès.";
                
                $data = [
                    'job_id' => (int) $job->id,
                    'client_id' => (int) $client->id,
                    'client_name' => htmlspecialchars($client->name, ENT_QUOTES, 'UTF-8'),
                    'action_type' => 'job_validated'
                ];
            } else { // rejected
                $title = 'Travail rejeté';
                // Clean and escape the data
                $cleanJobTitle = htmlspecialchars($job->title, ENT_QUOTES, 'UTF-8');
                $cleanClientName = htmlspecialchars($client->name, ENT_QUOTES, 'UTF-8');
                $cleanReason = $reason ? htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') : '';
                
                $message = "Le travail \"{$cleanJobTitle}\" a été rejeté par {$cleanClientName}. Veuillez apporter les corrections demandées.";
                if ($cleanReason) {
                    $message .= " Raison: " . $cleanReason;
                }
                
                $data = [
                    'job_id' => (int) $job->id,
                    'client_id' => (int) $client->id,
                    'client_name' => htmlspecialchars($client->name, ENT_QUOTES, 'UTF-8'),
                    'rejection_reason' => $cleanReason,
                    'action_type' => 'job_rejected'
                ];
            }
            
            Log::info("Creating worker notification", [
                'job_id' => $job->id,
                'worker_id' => $worker->id,
                'action' => $action,
                'title' => $title,
                'message' => $message
            ]);
            
            // Create notification with properly formatted data
            $notificationData = [
                'user_id' => (int) $worker->id,
                'title' => $title,
                'message' => $message,
                'data' => $data, // Let Eloquent handle JSON encoding
            ];
            
            $result = Notification::create($notificationData);

            Log::info("Worker notification created successfully", [
                'job_id' => $job->id,
                'worker_id' => $worker->id,
                'action' => $action
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error("Failed to create worker notification", [
                'job_id' => $job->id,
                'worker_id' => $job->worker_id ?? 'unknown',
                'action' => $action,
                'error' => $e->getMessage()
            ]);

            // Don't throw exception, just log the error
            // This ensures the main validation/rejection process continues even if notification fails
            return false;
        }
    }

}
