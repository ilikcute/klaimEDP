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
        Schema::table('claim_documents', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('checksum_sha256');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->float('gps_accuracy_meters')->nullable()->after('longitude');
            $table->timestamp('captured_at')->nullable()->after('gps_accuracy_meters');
            $table->string('location_address', 255)->nullable()->after('captured_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claim_documents', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'gps_accuracy_meters',
                'captured_at',
                'location_address',
            ]);
        });
    }
};
