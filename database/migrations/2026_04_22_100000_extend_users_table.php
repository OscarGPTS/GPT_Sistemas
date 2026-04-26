<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code')->nullable()->unique()->after('id');
            $table->string('department')->nullable()->after('email');
            $table->string('position')->nullable()->after('department');
            $table->foreignId('manager_id')->nullable()->after('position')
                ->constrained('users')->nullOnDelete();
            $table->string('phone')->nullable()->after('manager_id');
            $table->string('auth0_sub')->nullable()->unique()->after('phone');
            $table->boolean('is_active')->default(true)->after('auth0_sub');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn([
                'employee_code', 'department', 'position', 'manager_id',
                'phone', 'auth0_sub', 'is_active', 'deleted_at',
            ]);
        });
    }
};
