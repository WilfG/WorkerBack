<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminJobController extends Controller
{
    public function index()
    {
        $jobs = Job::with(['client', 'worker'])->paginate(10);
               foreach ($jobs as $job) {
            foreach (User::all() as $user) {
                if ($user->id === $job->client_id) {
                    $job->client_name = $user->name;
                }
                if ($user->id === $job->worker_id) {
                    $job->worker_name = $user->name;
                }
            }
        }
                // dd($jobs);
        return view('jobs.index', compact('jobs'));
    }

    public function show(Job $job)
    {
        $job->load(['client', 'worker', 'images']);
        return view('jobs.show', compact('job'));
    }

    public function destroy(Job $job)
    {
        try {
            // Supprimer les images associées
            foreach ($job->images as $image) {
                Storage::delete('public/' . $image->path);
                $image->delete();
            }

            $job->delete();
            return redirect()->route('jobs.index')
                ->with('success', 'Travail supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()->route('jobs.index')
                ->with('error', 'Une erreur est survenue lors de la suppression du travail');
        }
    }
}
