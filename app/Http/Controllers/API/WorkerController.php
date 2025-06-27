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
            $workers = User::where('role', 'worker')
                ->where('profession_id', $request->profession_id)
                ->with(['profession', 'ratings'])
                // ->withAvg('ratings', 'rating')
                ->withCount(['completedJobs'])
                ->get();
            Log::info('Fetched workers', ['count' => $workers->count()]);

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
}
