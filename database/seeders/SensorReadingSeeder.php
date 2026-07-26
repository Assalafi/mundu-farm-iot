<?php

namespace Database\Seeders;

use App\Models\PumpControl;
use App\Models\SensorReading;
use Illuminate\Database\Seeder;

class SensorReadingSeeder extends Seeder
{
    public function run(): void
    {
        $startTime = now()->subHours(24);

        for ($i = 0; $i < 48; $i++) {
            SensorReading::create([
                'sensor_type' => 'moisture',
                'value' => fake()->randomFloat(1, 25, 85),
                'unit' => '%',
                'recorded_at' => $startTime->copy()->addMinutes($i * 30),
            ]);

            SensorReading::create([
                'sensor_type' => 'soil_ph',
                'value' => fake()->randomFloat(1, 5.0, 8.0),
                'unit' => 'pH',
                'recorded_at' => $startTime->copy()->addMinutes($i * 30),
            ]);
        }

        PumpControl::create([
            'action' => 'on',
            'triggered_by' => 'manual',
            'triggered_at' => now()->subHours(12),
        ]);

        PumpControl::create([
            'action' => 'off',
            'triggered_by' => 'manual',
            'triggered_at' => now()->subHours(6),
        ]);

        PumpControl::create([
            'action' => 'on',
            'triggered_by' => 'auto',
            'triggered_at' => now()->subHour(),
        ]);
    }
}
