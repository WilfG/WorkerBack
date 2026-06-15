<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $fillable = [

        'client_id',
        'worker_id',
        'profession_id',
        'title',
        'description',
        'status',
        'price',
        'location',
        'deadline',
        'completed_at'
    ];

    protected $casts = [
       'date_start' => 'datetime',
        'deadline' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'price' => 'decimal:2'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function profession()
    {
        return $this->belongsTo(Profession::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    public function images()
    {
        return $this->hasMany(JobImage::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
    
     /**
     * Get the notifications for this job
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Scope to get jobs by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get jobs for a specific worker
     */
    public function scopeForWorker($query, $workerId)
    {
        return $query->where('worker_id', $workerId);
    }

    /**
     * Scope to get jobs for a specific client
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Check if job can be delivered
     */
    public function canBeDelivered(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if job can be validated
     */
    public function canBeValidated(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Mark job as delivered
     */
    public function markAsDelivered(): bool
    {
        if (!$this->canBeDelivered()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now()
        ]);
    }

    /**
     * Mark job as completed
     */
    public function markAsCompleted(): bool
    {
        if (!$this->canBeValidated()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now()
        ]);
    }

    /**
     * Reject delivery and return to in progress
     */
    public function rejectDelivery(string $reason = null): bool
    {
        if (!$this->canBeValidated()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'delivered_at' => null,
            'rejection_reason' => $reason
        ]);
    }
}
