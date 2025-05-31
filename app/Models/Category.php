<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

     public function categories()
    {
        return $this->belongsToMany(Category::class);
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
}
