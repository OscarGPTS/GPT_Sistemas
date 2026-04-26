<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // PR-2026-001
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->text('justification')->nullable();
            $table->enum('impact', ['low', 'medium', 'high'])->default('medium');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->decimal('budget_estimate', 14, 2)->nullable();
            $table->string('expected_area')->nullable();
            $table->date('desired_start_date')->nullable();
            $table->date('desired_end_date')->nullable();
            $table->enum('status', ['draft', 'submitted', 'in_review', 'approved', 'rejected', 'converted', 'cancelled'])
                ->default('draft');
            $table->foreignId('workflow_instance_id')->nullable()->constrained('workflow_instances')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('decided_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_requests');
    }
};
