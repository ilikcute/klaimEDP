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
        Schema::table('claim_bbm_details', function (Blueprint $table) {
            $table->decimal('last_odometer_km', 12, 2)->nullable()->after('vehicle_type');
            $table->decimal('current_odometer_km', 12, 2)->nullable()->after('last_odometer_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claim_bbm_details', function (Blueprint $table) {
            $table->dropColumn(['last_odometer_km', 'current_odometer_km']);
        });
    }
};
