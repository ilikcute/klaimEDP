<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->unique()->constrained('claims')->onDelete('cascade');
            $table->foreignId('processed_by')->constrained('users')->onDelete('restrict');
            $table->decimal('amount', 18, 2);
            $table->string('payment_method', 30)->default('BANK_TRANSFER');
            $table->string('bank_name', 150);
            $table->string('bank_account_number', 100);
            $table->string('bank_account_name', 150);
            $table->dateTime('payment_date')->nullable();
            $table->string('transfer_reference', 150)->nullable();
            $table->foreignId('payment_proof_document_id')->nullable()->constrained('claim_documents')->onDelete('set null');
            $table->string('status', 30)->default('PENDING'); // PENDING, PROCESSING, PAID, FAILED
            $table->string('idempotency_key', 100)->unique()->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
