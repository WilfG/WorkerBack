<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

     protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Get the users that belong to this country
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get workers (artisans) from this country
     */
    public function workers()
    {
        return $this->hasMany(User::class)
                    ->where('user_type', 'ARTISAN')
                    ->where('role', 'worker');
    }

    /**
     * Get clients from this country
     */
    public function clients()
    {
        return $this->hasMany(User::class)
                    ->where('user_type', 'CLIENT')
                    ->where('role', 'client');
    }
}
