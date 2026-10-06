<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->unique()->constrained('claims')->onDelete('cascade');
            $table->foreignId('workflow_definition_id')->constrained('workflow_definitions')->onDelete('restrict');
            $table->unsignedInteger('workflow_version');
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->onDelete('cascade');
            $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->onDelete('cascade');
            $table->foreignId('transition_id')->nullable()->constrained('workflow_transitions')->onDelete('set null');
            $table->string('from_state', 50);
            $table->string('action', 50);
            $table->string('to_state', 50);
            $table->foreignId('actor_id')->constrained('users')->onDelete('restrict');
            $table->string('actor_role', 100);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approval_policy_id')->nullable()->constrained('approval_policies')->onDelete('set null');
            $table->unsignedInteger('approval_policy_version')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['claim_id', 'created_at']);
            $table->index(['actor_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });

        Schema::create('claim_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->onDelete('cascade');
            $table->string('from_state', 50)->nullable();
            $table->string('to_state', 50);
            $table->string('action', 50);
            $table->foreignId('actor_id')->constrained('users')->onDelete('restrict');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['claim_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_status_histories');
        Schema::dropIfExists('workflow_actions');
        Schema::dropIfExists('workflow_instances');
    }
};
