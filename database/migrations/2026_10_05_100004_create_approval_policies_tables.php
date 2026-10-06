<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_stage_id')->constrained('workflow_stages')->onDelete('cascade');
            $table->string('name', 150);
            $table->unsignedInteger('version')->default(1);
            $table->string('approval_rule', 30)->default('ANY_OF'); // ANY_OF, ALL_OF, N_OF
            $table->unsignedInteger('minimum_approvals')->default(1);
            $table->integer('priority')->default(0);
            $table->decimal('min_amount', 18, 2)->nullable();
            $table->decimal('max_amount', 18, 2)->nullable();
            $table->string('claim_type', 30)->nullable();
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('approval_policy_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_policy_id')->constrained('approval_policies')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['approval_policy_id', 'role_id']);
        });

        Schema::create('approval_policy_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_policy_id')->constrained('approval_policies')->onDelete('cascade');
            $table->string('scope_type', 50); // DEPARTMENT, BRANCH, REGION
            $table->string('scope_value', 150);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_policy_scopes');
        Schema::dropIfExists('approval_policy_roles');
        Schema::dropIfExists('approval_policies');
    }
};
