<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->string('sensor_type');
            $table->decimal('value', 8, 2);
            $table->string('unit');
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index('sensor_type');
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
