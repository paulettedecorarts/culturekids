<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maze_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maze_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->boolean('completed')->default(false);
            $table->unsignedInteger('stars_earned')->default(0);
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->unsignedInteger('collectibles_found')->default(0);
            $table->unsignedInteger('attempts_count')->default(1);
            $table->json('path_taken')->nullable(); // [{row, col}] sequence of moves

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['maze_id', 'user_id']);
            $table->index(['user_id', 'completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maze_attempts');
    }
};
