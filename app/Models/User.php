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
        'subscription_end_date' => 'datetime',
        'is_subscribed' => 'boolean',

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


    // Add to User model's relationships:
    public function country()
    {
        return $this->belongsTo(Country::class);
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


    // Add this relationship method to your User model
    public function pushToken()
    {
        return $this->hasOne(UserPushToken::class);
    }

    // Add this relationship method to your User model  
    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    // Add this relationship method to your User model
    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->where('read', false);
    }

    // Add these methods to your User model for notification preferences
    public function getNotificationPreference($type)
    {
        // You can expand this to store user preferences in database
        return true; // For now, all notifications are enabled
    }

    public function shouldReceivePushNotifications()
    {
        return $this->pushToken !== null;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }


    // Add this method to check if subscription is active:
    public function hasActiveSubscription()
    {
        return $this->is_subscribed &&
            $this->subscription_end_date &&
            $this->subscription_end_date->isFuture();
    }

    // Add this method to get subscription status:
    public function getSubscriptionStatus()
    {
        if (!$this->is_subscribed) {
            return 'inactive';
        }

        if (!$this->subscription_end_date) {
            return 'active'; // Lifetime subscription
        }

        if ($this->subscription_end_date->isFuture()) {
            return 'active';
        }

        return 'expired';
    }


    public function workImages()
    {
        return $this->hasMany(UserWorkImage::class);
    }
    
        /**
     * Check if user has applied to a specific job
     *
     * @param Job $job
     * @return bool
     */
    public function hasApplied(Job $job): bool
    {
        return $this->applications()
            ->where('job_id', $job->id)
            ->exists();
    }
    
    /**
     * Get user's job applications
     */
    public function applications()
    {
        return $this->hasMany(Application::class, 'worker_id');
    }
}
