<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Profession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfessionController extends Controller
{
    public function index(Request $request)
    {
        Log::info('Fetching professions', ['request' => $request->all()]);
        // $query = User::where('role', 'worker')
        //     ->where('status', 1);

        // if ($request->has('profession_id')) {
        //     $query->where('profession_id', $request->profession_id);
        // }

        // $workers = $query->with(['ratings', 'profession'])
        //     ->withAvg('ratings', 'rating')
        //     ->withCount(['completedJobs' => function ($query) {
        //         $query->where('status', 'completed');
        //     }])
        //     ->get();
        // return response()->json($workers);

        $professions = Profession::orderBy('name')->get();
        Log::info('Professions fetched', ['count' => $professions->count()]);
        return response()->json($professions);
    }
}
