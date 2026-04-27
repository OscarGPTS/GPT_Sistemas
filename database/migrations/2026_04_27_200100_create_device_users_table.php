<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_users', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->nullable()->index();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('mailbox')->nullable();
            $table->string('print_code', 20)->unique();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Optional link to system user');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('is_active');
        });

        // Pivot to grant a device user access to specific printers (assets)
        Schema::create('device_user_asset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_user_id')->constrained('device_users')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->timestamp('granted_at')->useCurrent();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['device_user_id', 'asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_user_asset');
        Schema::dropIfExists('device_users');
    }
};
