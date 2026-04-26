<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name')->default('Tablero principal');
            $table->boolean('is_default')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'is_default']);
        });

        Schema::create('board_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('boards')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 50);
            $table->string('color', 16)->default('#94a3b8');
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('wip_limit')->nullable();
            $table->boolean('is_done_column')->default(false);
            $table->timestamps();

            $table->index(['board_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_columns');
        Schema::dropIfExists('boards');
    }
};
