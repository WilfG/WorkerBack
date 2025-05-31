<?php

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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/webhook/stripe', [SubscriptionController::class, 'handleWebhook']);
Route::get('/user', [AuthController::class, 'user']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/subscription/create-checkout', [SubscriptionController::class, 'createCheckout']);
    Route::get('/workers', [WorkerController::class, 'index']);
    // Route::post('/subscription/initialize-payment', [SubscriptionController::class, 'initializePayment']);
    Route::post('/subscription/confirm', [SubscriptionController::class, 'confirm']);
    Route::post('/subscription/process-payment', [SubscriptionController::class, 'processPayment']);
});
