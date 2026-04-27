<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('color', 16)->default('#6366f1');
            $table->text('description')->nullable();
            $table->decimal('monthly_budget', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // EXP-202604-0001
            $table->foreignId('category_id')->constrained('expense_categories')->restrictOnDelete();
            $table->enum('type', ['recurring', 'variable'])->default('variable');
            $table->string('concept');
            $table->text('notes')->nullable();
            $table->decimal('amount', 14, 2);
            $table->date('expense_date');
            $table->string('supplier')->nullable();
            $table->string('invoice_number')->nullable();
            $table->enum('payment_method', ['cash', 'transfer', 'card', 'check', 'other'])->default('transfer');
            // Optional links
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('equipment_request_id')->nullable()->constrained('equipment_requests')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['expense_date', 'category_id']);
            $table->index('type');
        });

        Schema::create('expense_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_documents');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
