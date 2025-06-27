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
        'price' => 'decimal:2',
        'completed_at' => 'datetime',
        'deadline' => 'datetime',
    ];

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
}
