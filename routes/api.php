<?php

use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\JobController;
use App\Http\Controllers\API\ProfessionController;
use App\Http\Controllers\API\WorkerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionController;

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
Route::get('/user', [AuthController::class, 'user']);
Route::post('/auth/complete-profile', [AuthController::class, 'completeProfile']);
Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/subscription/create-checkout', [SubscriptionController::class, 'createCheckout']);


    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/workers', [WorkerController::class, 'index']);
    Route::get('/workers/{id}', [WorkerController::class, 'show']);
    // Route::post('/subscription/initialize-payment', [SubscriptionController::class, 'initializePayment']);
    Route::post('/subscription/confirm', [SubscriptionController::class, 'confirm']);
    Route::post('/subscription/process-payment', [SubscriptionController::class, 'processPayment']);
    Route::get('/categories/{category}/professions', [CategoryController::class, 'professions']);

    // Worker job routes
    Route::get('/jobs/current', [JobController::class, 'getCurrentJobs']);
    Route::get('/jobs/completed', [JobController::class, 'getCompletedJobs']);
    // Client job routes
    Route::get('/jobs/posted', [JobController::class, 'getPostedJobs']);
    Route::post('/jobs', [JobController::class, 'store']);

    Route::get('/jobs/available', [JobController::class, 'available']);

    Route::post('/jobs/{job}/apply', [JobController::class, 'apply']);
    Route::get('/jobs/{job}/applications', [JobController::class, 'applications']);
    Route::post('/jobs/{job}/hire', [JobController::class, 'hire']);
    Route::get('/jobs/{job}', [JobController::class, 'show']);
});
