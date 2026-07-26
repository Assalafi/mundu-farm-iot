<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'sensor_type',
        'value',
        'unit',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    public static function latestMoisture()
    {
        return static::where('sensor_type', 'moisture')->latest('recorded_at')->value('value');
    }

    public static function latestPh()
    {
        return static::where('sensor_type', 'soil_ph')->latest('recorded_at')->value('value');
    }

    public static function moistureHistory(int $limit = 50)
    {
        return static::where('sensor_type', 'moisture')
            ->latest('recorded_at')
            ->limit($limit)
            ->get();
    }

    public static function phHistory(int $limit = 50)
    {
        return static::where('sensor_type', 'soil_ph')
            ->latest('recorded_at')
            ->limit($limit)
            ->get();
    }
}
