<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'traccar_id',
        'name',
        'status',
        'last_update'
    ];

    protected $casts = [
        'traccar_id' => 'integer',
        'last_update' => 'datetime'
    ];

    /**
     * Get the positions for this device
     */
    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Get the latest position for this device
     */
    public function latestPosition()
    {
        return $this->hasOne(Position::class)->latest('device_time');
    }

    /**
     * Scope to get online devices
     */
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    /**
     * Check if device is online
     */
    public function isOnline()
    {
        return $this->status === 'online';
    }
}
