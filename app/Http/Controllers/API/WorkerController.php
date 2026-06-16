<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use App\Models\UserWorkImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkerController extends Controller
{
    /**
     * Display a listing of the workers.
     */


    
    public function index(Request $request)
    {
    Log::info('Fetching workers', ['request' => $request->all()]);
    try {
        $query = User::where('role', 'worker')
            ->with(['profession', 'ratings'])
            ->withCount(['completedJobs']);

        // profession_id is always required
        if (!$request->has('profession_id')) {
            return response()->json(['error' => 'Profession ID is required'], 400);
        }
        $query->where('profession_id', $request->profession_id);

        // country_id is optional - if provided, filter by it
        if ($request->has('country_id') && $request->country_id) {
            $query->where('country_id', $request->country_id);
            Log::info('Filtering by country', ['country_id' => $request->country_id]);
        }

        $workers = $query->get();
        Log::info('Fetched workers', [
            'count' => $workers->count(),
            'profession_id' => $request->profession_id,
            'country_id' => $request->country_id ?? 'all'
        ]);

        return response()->json($workers);
    } catch (\Exception $e) {
        Log::error('Worker search error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error fetching workers',
            'error' => $e->getMessage()
        ], 500);
    }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       $worker = User::where('role', 'worker')
            ->with(['profession', 'ratings'])
            ->withAvg('ratings', 'rating')
            ->withCount(['completedJobs'])
            ->findOrFail($id);
        // Get work images (array of URLs)
        $workImages = UserWorkImage::where('user_id', $worker->id)->pluck('image')->toArray();

        // Optionally, get completed jobs
        $completedJobs = Job::where('worker_id', $worker->id)
            ->where('status', 'completed')
            ->get(['title', 'description']);

        // Add extra fields to user object
        $worker->certification = $worker->certification; // already present if column exists
        $worker->works = $workImages;
        $worker->completed_jobs = $completedJobs;
        return response()->json($worker);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function getWorkerCountriesByProfession(Request $request)
    {
        Log::info('Fetching worker countries by profession', ['request' => $request->all()]);
        try {
            $professionId = $request->query('profession_id');

            if (!$professionId) {
                return response()->json(['error' => 'Profession ID is required'], 400);
            }

            // Get unique countries from workers in this profession
            $countries = DB::table('users')
                ->join('countries', 'users.country_id', '=', 'countries.id')
                ->where('users.user_type', 'ARTISAN') // or 'worker' depending on your setup
                ->where('users.profession_id', $professionId)
                ->whereNotNull('users.country_id')
                ->select('countries.id', 'countries.name')
                ->distinct()
                ->orderBy('countries.name')
                ->get();

            return response()->json($countries);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch countries',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
