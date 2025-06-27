<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profession extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'category', 'icon'];

    public function workers()
    {
        return $this->hasMany(User::class, 'profession_id');
    }

      public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
