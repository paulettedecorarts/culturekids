<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('language_activity_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->boolean('completed')->default(false);
            $table->unsignedInteger('stars_earned')->default(0);
            $table->unsignedInteger('score')->default(0);          // 0-100
            $table->unsignedInteger('attempts_count')->default(1); // How many tries
            $table->unsignedInteger('time_spent_seconds')->default(0);

            // Per-word results (for audio_match, proverb_jumble etc.)
            $table->json('word_results')->nullable();

            // For speak_back - path to recorded audio
            $table->string('recording_path')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['language_activity_id', 'user_id']);
            $table->index(['user_id', 'completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('language_activity_attempts');
    }
};
