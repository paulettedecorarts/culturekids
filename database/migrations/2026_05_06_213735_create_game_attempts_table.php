<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->boolean('completed')->default(false);
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('stars_earned')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->unsignedInteger('wrong_answers')->default(0);
            $table->unsignedInteger('lives_remaining')->default(3);
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->unsignedInteger('attempts_count')->default(1);

            // Per-question results
            $table->json('question_results')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['game_id', 'user_id']);
            $table->index(['user_id', 'completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_attempts');
    }
};
