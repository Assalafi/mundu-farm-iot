<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pump_controls', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->string('triggered_by')->default('manual');
            $table->timestamp('triggered_at')->useCurrent();
            $table->timestamps();

            $table->index('triggered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pump_controls');
    }
};
