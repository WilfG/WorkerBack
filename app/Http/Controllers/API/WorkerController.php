<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkerController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index(Request $request)
    {
        try {
            $query = User::where('user_type', 'ARTISAN')
                        ->where('status', true);

            if ($request->filled('category')) {
                $query->whereHas('categories', function($q) use ($request) {
                    $q->where('name', $request->category);
                });
            }

            $workers = $query->select([
                'users.id',
                'users.name',
                'users.email',
                'users.phone_number',
                'users.profile_photo as avatar',
                'users.description',
                'users.hourly_rate',
                'users.years_experience',
                DB::raw('(SELECT COUNT(*) FROM jobs WHERE jobs.worker_id = users.id AND jobs.status = "completed") as completed_jobs'),
                DB::raw('COALESCE((SELECT AVG(rating) FROM ratings WHERE ratings.worker_id = users.id), 0) as average_rating')
            ])
            ->withCount(['ratings as total_ratings'])
            ->with(['categories:id,name'])
            ->get();

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
        //
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
}
