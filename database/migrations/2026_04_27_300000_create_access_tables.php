<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('color', 16)->default('#6366f1');
            $table->json('default_fields')->nullable(); // suggested extra field labels
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('accesses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ACC-2026-0001
            $table->foreignId('type_id')->constrained('access_types')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('hostname')->nullable();
            $table->string('ip', 45)->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('url')->nullable();
            // Sensitive fields — encrypted via cast
            $table->text('username')->nullable();
            $table->text('password')->nullable();
            $table->text('notes')->nullable();
            // Optional links
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            // Rotation
            $table->dateTime('last_rotated_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['type_id', 'is_active']);
            $table->index('last_rotated_at');
        });

        // Type-specific extra fields (small EAV layer for flexibility)
        Schema::create('access_extra_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_id')->constrained('accesses')->cascadeOnDelete();
            $table->string('field_name');  // e.g. ssid, mode, pin
            $table->string('field_label'); // human label
            $table->text('field_value')->nullable(); // encrypted if sensitive
            $table->boolean('is_sensitive')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['access_id', 'field_name']);
        });

        // Immutable audit log of credential interactions (CRITICAL)
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('access_id')->constrained('accesses')->cascadeOnDelete();
            $table->enum('action', ['create', 'view_metadata', 'reveal_request', 'reveal', 'copy', 'update', 'rotate', 'delete', 'failed_otp'])->index();
            $table->string('field')->nullable(); // password, pin, etc.
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            // No updated_at — these records are immutable
        });

        Schema::create('otp_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('purpose')->default('access.reveal'); // future-proof
            $table->foreignId('access_id')->nullable()->constrained('accesses')->cascadeOnDelete();
            $table->string('code_hash'); // we store hashed code, not plain
            $table->dateTime('expires_at');
            $table->dateTime('used_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'purpose', 'used_at']);
        });

        Schema::create('access_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_id')->constrained('accesses')->cascadeOnDelete();
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
        Schema::dropIfExists('access_documents');
        Schema::dropIfExists('otp_tokens');
        Schema::dropIfExists('access_logs');
        Schema::dropIfExists('access_extra_fields');
        Schema::dropIfExists('accesses');
        Schema::dropIfExists('access_types');
    }
};
