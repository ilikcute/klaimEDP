<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('vehicle_type', 30)->index(); // MOTOR, CAR
            $table->string('plate_number', 20)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bbm_standards', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_type', 30)->index(); // MOTOR, CAR
            $table->decimal('standard_km_per_liter', 10, 2);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('health_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->decimal('annual_limit', 18, 2);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_policies');
        Schema::dropIfExists('bbm_standards');
        Schema::dropIfExists('vehicles');
    }
};
