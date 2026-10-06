<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_stage_id')->nullable()->constrained('workflow_stages')->onDelete('cascade');
            $table->string('name', 150);
            $table->unsignedInteger('duration_minutes');
            $table->string('calendar_type', 30)->default('WORKING_DAYS'); // CALENDAR_DAYS, WORKING_DAYS
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sla_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->onDelete('cascade');
            $table->foreignId('workflow_stage_id')->constrained('workflow_stages')->onDelete('restrict');
            $table->foreignId('sla_policy_id')->nullable()->constrained('sla_policies')->onDelete('set null');
            $table->dateTime('started_at');
            $table->dateTime('due_at');
            $table->dateTime('completed_at')->nullable();
            $table->string('status', 30)->default('ON_TIME'); // ON_TIME, AT_RISK, BREACHED
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->timestamps();

            $table->index(['claim_id', 'status']);
            $table->index(['status', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_instances');
        Schema::dropIfExists('sla_policies');
    }
};
