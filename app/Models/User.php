<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'role' => 'client'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }


    /**
     * Get the completed jobs for the worker
     */
    public function completedJobs()
    {
        return $this->hasMany(Job::class, 'worker_id')
            ->where('status', 'completed');
    }

    /**
     * Get the worker's profession
     */
    public function profession()
    {
        return $this->belongsTo(Profession::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'worker_id');
    }

    protected $appends = ['rating'];

    public function getRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class, 'worker_id');
    }
}
