<?php

namespace App\Http\Controllers;

use App\Models\UserPushToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PushTokenController extends Controller
{
    /**
     * Register or update push token for authenticated user
     */
    public function registerPushToken(Request $request)
    {
        try {
            // Get authenticated user
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'push_token' => 'required|string|max:1000',
                'platform' => 'required|in:android,ios,web'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Log for debugging
            Log::info('Registering push token', [
                'user_id' => $user->id,
                'platform' => $request->platform,
                'token_preview' => substr($request->push_token, 0, 50) . '...'
            ]);

            // Create or update push token
            $pushToken = UserPushToken::createOrUpdateForUser(
                $user->id,
                $request->push_token,
                $request->platform
            );

            return response()->json([
                'success' => true,
                'message' => 'Push token registered successfully',
                'data' => [
                    'id' => $pushToken->id,
                    'user_id' => $pushToken->user_id,
                    'platform' => $pushToken->platform,
                    'active' => $pushToken->active,
                    'created_at' => $pushToken->created_at,
                    'updated_at' => $pushToken->updated_at
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Push token registration failed', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register push token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user's push tokens
     */
    public function getUserTokens(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $tokens = UserPushToken::where('user_id', $user->id)
                                  ->orderBy('created_at', 'desc')
                                  ->get()
                                  ->map(function ($token) {
                                      return [
                                          'id' => $token->id,
                                          'platform' => $token->platform,
                                          'active' => $token->active,
                                          'last_used_at' => $token->last_used_at,
                                          'created_at' => $token->created_at,
                                          'token_preview' => substr($token->push_token, 0, 50) . '...'
                                      ];
                                  });

            return response()->json([
                'success' => true,
                'data' => $tokens
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get user tokens', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get push tokens'
            ], 500);
        }
    }

    /**
     * Deactivate a specific push token
     */
    public function deactivateToken(Request $request, $tokenId)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $token = UserPushToken::where('id', $tokenId)
                                 ->where('user_id', $user->id)
                                 ->first();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not found'
                ], 404);
            }

            $token->deactivate();

            return response()->json([
                'success' => true,
                'message' => 'Token deactivated successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to deactivate token', [
                'user_id' => $user->id ?? 'unknown',
                'token_id' => $tokenId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate token'
            ], 500);
        }
    }

    /**
     * Clean up old inactive tokens (admin function)
     */
    public function cleanupOldTokens(Request $request)
    {
        try {
            $daysOld = $request->input('days_old', 30);
            $deletedCount = UserPushToken::cleanupOldTokens($daysOld);

            Log::info('Cleaned up old push tokens', [
                'deleted_count' => $deletedCount,
                'days_old' => $daysOld
            ]);

            return response()->json([
                'success' => true,
                'message' => "Cleaned up {$deletedCount} old tokens",
                'data' => [
                    'deleted_count' => $deletedCount,
                    'days_old' => $daysOld
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to cleanup old tokens', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup old tokens'
            ], 500);
        }
    }
}
