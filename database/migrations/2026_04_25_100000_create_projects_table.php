<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // PRJ-2026-001
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('area')->nullable(); // Área solicitante
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['planning', 'in_progress', 'paused', 'completed', 'cancelled'])
                ->default('planning');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 14, 2)->nullable();
            $table->string('color', 16)->default('#6366f1');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('archived_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['manager', 'collaborator', 'observer'])->default('collaborator');
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
    }
};
