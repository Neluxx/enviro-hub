<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->nullable()->constrained('nodes')->onDelete('set null');
            $table->uuid('node_uuid');
            $table->decimal('temperature', 5, 2);
            $table->decimal('humidity', 5, 2);
            $table->integer('pressure');
            $table->integer('carbon_dioxide')->nullable();
            $table->timestamp('measured_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};
