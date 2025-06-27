<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
            'budget' => 'nullable|numeric',
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
        $job = Job::findOrFail($jobId);
        $hasApplied = false;

        if (auth()->user() && auth()->user()->role === 'worker') {
            $hasApplied = $job->applications()->where('worker_id', auth()->id())->exists();
        }

        return response()->json([
            'job' => $job,
            'hasApplied' => $hasApplied,
        ]);
    }
}
