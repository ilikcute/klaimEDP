<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100);
            $table->string('name', 150);
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['code', 'version']);
        });

        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_definition_id')->constrained('workflow_definitions')->onDelete('cascade');
            $table->string('code', 100);
            $table->string('name', 150);
            $table->unsignedInteger('sequence');
            $table->string('stage_type', 50); // INITIAL, REVIEW, VERIFICATION, APPROVAL, PAYMENT, COMPLETED, TERMINAL
            $table->timestamps();

            $table->unique(['workflow_definition_id', 'code']);
        });

        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_stage_id')->constrained('workflow_stages')->onDelete('cascade');
            $table->string('action', 50);
            $table->string('from_state', 50);
            $table->string('to_state', 50);
            $table->string('required_permission', 150)->nullable();
            $table->timestamps();

            $table->index(['from_state', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflow_stages');
        Schema::dropIfExists('workflow_definitions');
    }
};
