<?php

use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminJobController;
use App\Http\Controllers\AdminProfessionController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Models\Job;
use App\Models\Profession;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // Artisan::call('migrate:fresh');
    return view('auth.login');
});

Route::post('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('/email/resend-verification', [AuthController::class, 'resendVerification'])->name('verification.resend');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        // die('You are logged in!');
        $totClients = User::where('role', 'client')->count();
        $totWorkers = User::where('role', 'worker')->count();
        $totJobs = Job::count();
        
        // Recettes du jour (subscriptions approuvées)
        $totDayRecettes = Subscription::where('status', 'approved')
                                      ->whereDate('created_at', now())
                                      ->sum('amount');
        
        // Recettes du mois courant (subscriptions approuvées)
        $totMonthRecettes = Subscription::where('status', 'approved')
                                        ->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->sum('amount');
        
        // Recettes des 3 derniers mois (subscriptions approuvées)
        $totThreeMonthsRecettes = Subscription::where('status', 'approved')
                                              ->where('created_at', '>=', now()->subMonths(3))
                                              ->sum('amount');
        
        // Recettes de l'année en cours (subscriptions approuvées)
        $totYearRecettes = Subscription::where('status', 'approved')
                                       ->whereYear('created_at', now()->year)
                                       ->sum('amount');
        
        // Recettes de tous les temps (subscriptions approuvées)
        $totAllTimeRecettes = Subscription::where('status', 'approved')->sum('amount');
        
        $professionsPopulaires = Profession::withCount('jobs')->orderBy('jobs_count', 'desc')->take(5)->get();
        // dd($professionsPopulaires);
        return view('new_dashboard', compact('totClients', 'totWorkers', 'totJobs', 'totDayRecettes', 'totMonthRecettes', 'totThreeMonthsRecettes', 'totYearRecettes', 'totAllTimeRecettes', 'professionsPopulaires'));
    })->name('dashboard');


    Route::resource('users', AdminUserController::class);
    Route::resource('categories', AdminCategoryController::class);
    Route::resource('professions', AdminProfessionController::class);
    Route::resource('jobs', AdminJobController::class);
    // Add more as needed

    // Route::prefix('gestion_utilisateur')->group(function () {
    //     Route::resource('users', UsersController::class);
    //     Route::post('/password-update-request', [UsersController::class, 'passwordUpdateRequest'])->name('password_update_request');
    //     Route::get('update-password', function () {
    //         return view('auth.reset-password');
    //     })->name('update-password');
    //     Route::post('reset_password', [UsersController::class, 'reset_password'])->name('reset_password');
    //     Route::get('user/verify/{token}', [UsersController::class, 'verifyUser']);
    // });
});
