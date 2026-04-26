<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->enum('type', ['preventive', 'corrective'])->default('preventive');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('frequency', ['monthly', 'quarterly', 'biannual', 'annual', 'custom'])->default('annual');
            $table->integer('interval_days')->nullable();
            $table->date('next_due_at');
            $table->date('last_run_at')->nullable();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'next_due_at']);
        });

        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('maintenance_schedules')->nullOnDelete();
            $table->enum('type', ['preventive', 'corrective'])->default('preventive');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('scheduled_date');
            $table->date('performed_date')->nullable();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('provider')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->text('result_notes')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'status']);
            $table->index('scheduled_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
        Schema::dropIfExists('maintenance_schedules');
    }
};
