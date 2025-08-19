<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'latitude',
        'longitude',
        'speed',
        'device_time',
        'attributes'
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'speed' => 'integer',
        'device_time' => 'datetime',
        'attributes' => 'array'
    ];

    /**
     * Get the device associated with this position
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Scope to get latest positions for each device
     */
    public function scopeLatest($query)
    {
        return $query->whereIn('id', function($subQuery) {
            $subQuery->selectRaw('MAX(id)')
                     ->from('positions')
                     ->groupBy('device_id');
        });
    }

    /**
     * Scope to get positions within a time range
     */
    public function scopeWithinTimeRange($query, $minutes = 60)
    {
        return $query->where('device_time', '>=', Carbon::now()->subMinutes($minutes));
    }

    /**
     * Get formatted device time
     */
    public function getFormattedDeviceTimeAttribute()
    {
        return $this->device_time ? $this->device_time->format('Y-m-d H:i:s') : null;
    }

    /**
     * Get speed in km/h
     */
    public function getSpeedKmhAttribute()
    {
        return $this->speed ? round($this->speed, 1) : 0;
    }
}
