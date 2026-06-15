<?php

use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\JobController;
use App\Http\Controllers\API\ProfessionController;
use App\Http\Controllers\API\WorkerController;
use App\Http\Controllers\API\RatingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PushTokenController;
use App\Http\Controllers\FedaPayController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/professions', [ProfessionController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/webhook/stripe', [SubscriptionController::class, 'handleWebhook']);
Route::post('/profile/update', [AuthController::class, 'updateProfile']);
Route::get('/user', [AuthController::class, 'user']);
Route::post('/auth/complete-profile', [AuthController::class, 'completeProfile']);
Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);
Route::get('/countries', [CountryController::class, 'index']);
<<<<<<< HEAD
Route::post('/fedapay/callback', [FedaPayController::class, 'handleCallback']);

=======
>>>>>>> 5ee77f1eea3300f198cf687a3149d2cab64347a2

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/subscription/create-checkout', [SubscriptionController::class, 'createCheckout']);
    Route::get('/user/profile', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });


    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/workers', [WorkerController::class, 'index']);
    Route::get('/workers/countries', [WorkerController::class, 'getWorkerCountriesByProfession']);
    Route::get('/workers/{id}', [WorkerController::class, 'show']);
    Route::get('/workers/stats-by-country', [WorkerController::class, 'getWorkerStatsByCountry']);
    // Route::get('/workers/countries', [WorkerController::class, 'getCountriesWithWorkers']);
    
    // Route::get('/countries', [CountryController::class, 'index']);
    Route::get('/countries/{id}', [CountryController::class, 'show']);
    Route::get('/workers/{id}', [WorkerController::class, 'show']);
    // Route::post('/subscription/initialize-payment', [SubscriptionController::class, 'initializePayment']);
    Route::post('/subscription/confirm', [SubscriptionController::class, 'confirm']);
    Route::post('/subscription/process-payment', [SubscriptionController::class, 'processPayment']);
    Route::get('/categories/{category}/professions', [CategoryController::class, 'professions']);

    // Worker job routes
     Route::post('/jobs/{job}/deliver', [JobController::class, 'deliver']);
    Route::post('/jobs/{job}/validate', [JobController::class, 'validateJob']);
    Route::post('/jobs/{job}/reject', [JobController::class, 'reject']);
   
    Route::get('/jobs/current', [JobController::class, 'getCurrentJobs']);
    Route::get('/jobs/completed', [JobController::class, 'getCompletedJobs']);
    // Client job routes
    Route::get('/jobs/posted', [JobController::class, 'getPostedJobs']);
    Route::post('/jobs', [JobController::class, 'store']);

    Route::get('/jobs/available', [JobController::class, 'available']);

    Route::post('/jobs/{job}/apply', [JobController::class, 'apply']);
    Route::get('/jobs/{job}/applications', [JobController::class, 'applications']);
    Route::post('/jobs/{job}/hire', [JobController::class, 'hire']);
Route::post('/jobs/{id}/cancel-hire', [JobController::class, 'cancelHire']);
    Route::get('/jobs/{job}', [JobController::class, 'show']);
    
    // Push Token Management
      // Register or update push token for authenticated user
    Route::post('/push-tokens/register', [PushTokenController::class, 'registerPushToken']);
    
    // Get current user's push tokens
    Route::get('/push-tokens/my-tokens', [PushTokenController::class, 'getUserTokens']);
    
    // Deactivate a specific push token
    Route::delete('/push-tokens/{tokenId}/deactivate', [PushTokenController::class, 'deactivateToken']);
    
    
    
    Route::post('/users/register-push-token', [PushTokenController::class, 'registerPushToken']);
    Route::delete('/users/remove-push-token', [PushTokenController::class, 'removePushToken']);
    Route::get('/users/{user}', [UsersController::class, 'show']);

    
    // Notification Management
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Notification Triggers
    Route::post('/notifications/notify-workers-new-job', [NotificationController::class, 'notifyWorkersNewJob']);
    Route::post('/notifications/notify-client-worker-applied', [NotificationController::class, 'notifyClientWorkerApplied']);
    Route::post('/notifications/notify-worker-hired', [NotificationController::class, 'notifyWorkerHired']);
    
    // Ratings
  Route::post('/ratings', [RatingController::class, 'store']);
  Route::get('/ratings/worker/{workerId}', [RatingController::class, 'getRatingsByWorker']);
  Route::get('/ratings/client/{clientId}', [RatingController::class, 'getRatingsByClient']);
  Route::get('/ratings/job/{jobId}', [RatingController::class, 'getRatingsByJob']);
  
 
  // Create new payment transaction
  Route::post('/fedapay/transactions', [FedaPayController::class, 'createTransaction']);

  // Get transaction status
  Route::get('/fedapay/transactions/{transactionId}/status', [FedaPayController::class, 'getTransactionStatus']);

   // Check subscription status
    Route::get('/subscription/status', function () {
        $user = auth()->user();
        return response()->json([
            'is_subscribed' => $user->is_subscribed ?? false,
            'subscription_end_date' => $user->subscription_end_date,
            'remaining_days' => $user->subscription_end_date ? 
                now()->diffInDays($user->subscription_end_date, false) : 0
        ]);
    });
    
    // Cancel subscription
    Route::post('/subscription/cancel', function () {
        $user = auth()->user();
        $user->update([
            'is_subscribed' => false,
            'subscription_end_date' => null
        ]);
        
        return response()->json(['message' => 'Subscription cancelled successfully']);
    });
    
    // Get transaction history
    Route::get('/transactions', function () {
        $user = auth()->user();
        $transactions = $user->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return response()->json($transactions);
    });

});
