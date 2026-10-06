<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_number', 50)->unique();
            $table->foreignId('requester_id')->constrained('users')->onDelete('restrict');
            $table->string('claim_type', 30); // BBM, TOL, HEALTH
            $table->date('claim_date');
            $table->text('description')->nullable();
            $table->decimal('total_amount', 18, 2)->default(0.00);
            $table->string('current_state', 50)->default('DRAFT');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['requester_id', 'claim_date']);
            $table->index(['current_state', 'created_at']);
            $table->index(['current_state', 'updated_at']);
            $table->index(['claim_type', 'claim_date']);
            $table->index(['current_state', 'claim_type', 'created_at']);
        });

        Schema::create('claim_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->onDelete('cascade');
            $table->string('receipt_number', 100)->nullable();
            $table->date('transaction_date');
            $table->string('merchant', 200);
            $table->decimal('amount', 18, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('claim_id');
            $table->index('receipt_number');
            $table->index('transaction_date');
        });

        Schema::create('claim_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->onDelete('cascade');
            $table->foreignId('claim_receipt_id')->nullable()->constrained('claim_receipts')->onDelete('set null');
            $table->string('document_type', 50); // RECEIPT, FUEL_INVOICE, MEDICAL_LETTER, PRESCRIPTION, PAYMENT_PROOF, SUPPORTING_DOC
            $table->string('status', 30)->default('PENDING'); // PENDING, RECEIVED, VERIFIED, REJECTED, ARCHIVED
            $table->string('original_filename', 255);
            $table->string('storage_disk', 100)->default('local');
            $table->string('storage_path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->char('checksum_sha256', 64)->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->dateTime('received_at')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->index('claim_id');
            $table->index('claim_receipt_id');
            $table->index('checksum_sha256');
            $table->index(['document_type', 'status']);
        });

        Schema::create('claim_bbm_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->unique()->constrained('claims')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('restrict');
            $table->string('vehicle_type', 30); // MOTOR, CAR
            $table->decimal('distance_km', 12, 2);
            $table->decimal('fuel_liter', 12, 2);
            $table->decimal('actual_km_per_liter', 12, 4);
            $table->decimal('standard_km_per_liter', 10, 2);
            $table->decimal('variance_km_per_liter', 12, 4);
            $table->string('standard_snapshot_source', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('claim_health_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->unique()->constrained('claims')->onDelete('cascade');
            $table->foreignId('health_policy_id')->constrained('health_policies')->onDelete('restrict');
            $table->decimal('policy_limit_snapshot', 18, 2);
            $table->unsignedSmallInteger('claim_year');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_health_details');
        Schema::dropIfExists('claim_bbm_details');
        Schema::dropIfExists('claim_documents');
        Schema::dropIfExists('claim_receipts');
        Schema::dropIfExists('claims');
    }
};
