<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserPushToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'push_token',
        'platform',
        'active',
        'last_used_at'
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the push token
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get tokens by platform
     */
    public function scopePlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Update the last used timestamp
     */
    public function updateLastUsed()
    {
        $this->update(['last_used_at' => Carbon::now()]);
    }

    /**
     * Deactivate this token
     */
    public function deactivate()
    {
        $this->update(['active' => false]);
    }

    /**
     * Create or update push token for a user
     */
    public static function createOrUpdateForUser($userId, $pushToken, $platform = 'android')
    {
        // Deactivate any existing active tokens for this user
        static::where('user_id', $userId)
              ->where('active', true)
              ->update(['active' => false]);

        // Create new active token
        return static::create([
            'user_id' => $userId,
            'push_token' => $pushToken,
            'platform' => $platform,
            'active' => true,
            'last_used_at' => Carbon::now()
        ]);
    }

    /**
     * Get active push token for user
     */
    public static function getActiveTokenForUser($userId, $platform = null)
    {
        $query = static::where('user_id', $userId)->where('active', true);
        
        if ($platform) {
            $query->where('platform', $platform);
        }
        
        return $query->first();
    }

    /**
     * Clean up old inactive tokens (optional maintenance)
     */
    public static function cleanupOldTokens($daysOld = 30)
    {
        return static::where('active', false)
                    ->where('updated_at', '<', Carbon::now()->subDays($daysOld))
                    ->delete();
    }
}
