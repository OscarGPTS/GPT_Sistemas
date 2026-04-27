<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ER-2026-0001
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('justification')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', [
                'draft',
                'in_review',
                'approved',
                'rejected',
                'purchasing',
                'purchased',
                'delivered',
                'cancelled',
            ])->default('draft');
            $table->foreignId('workflow_instance_id')->nullable()->constrained('workflow_instances')->nullOnDelete();
            // IT validation fields
            $table->foreignId('it_validator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('it_validated_at')->nullable();
            $table->text('it_notes')->nullable();
            $table->string('preferred_supplier')->nullable();
            $table->decimal('estimated_cost', 14, 2)->nullable();
            // Purchase fields (caja chica)
            $table->foreignId('buyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('purchased_at')->nullable();
            $table->string('actual_supplier')->nullable();
            $table->decimal('actual_cost', 14, 2)->nullable();
            $table->string('invoice_number')->nullable();
            // Delivery fields
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('delivered_to')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            // Optional links
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index(['requester_id', 'status']);
        });

        Schema::create('equipment_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_request_id')->constrained('equipment_requests')->cascadeOnDelete();
            $table->string('type'); // mouse, keyboard, RAM, etc.
            $table->string('description');
            $table->string('suggested_model')->nullable();
            $table->string('purchase_link')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            // IT may override
            $table->string('approved_model')->nullable();
            $table->decimal('estimated_unit_cost', 12, 2)->nullable();
            $table->decimal('actual_unit_cost', 12, 2)->nullable();
            // Asset linkage when delivered (one of the units)
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->boolean('is_inventoriable')->default(true);
            $table->timestamps();
        });

        Schema::create('equipment_request_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_request_id')->constrained('equipment_requests')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('kind', ['invoice', 'receipt', 'delivery_evidence', 'other'])->default('other');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();

            $table->index(['equipment_request_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_request_documents');
        Schema::dropIfExists('equipment_request_items');
        Schema::dropIfExists('equipment_requests');
    }
};
