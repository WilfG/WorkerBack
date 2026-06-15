<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;;

use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class RatingController extends Controller
{
    public function store(Request $request)
    {
          try {
            Log::info('Creating rating', ['user_id' => Auth::id(), 'data' => $request->all()]);
            $validator = Validator::make($request->all(), [
                'worker_id' => 'required|exists:users,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:500',
                'job_id' => 'required|exists:jobs,id',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            Log::info('Validation passed', ['user_id' => Auth::id()]);
            $rating = Rating::create([
                'client_id' => Auth::id(),
                'worker_id' => $request->worker_id,
                'job_id' => $request->job_id, // Assuming job_id is passed in the request
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);

            if (!$rating) {
                return response()->json([
                    'message' => 'Failed to submit rating'
                ], 500);
            }
            Log::info('Rating created successfully', ['rating_id' => $rating->id, 'job_id' => $rating->job_id, 'user_id' => Auth::id()]);
            return response()->json([
                'message' => 'Rating submitted successfully',
                'rating' => $rating
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    
    }
}