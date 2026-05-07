<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('order_index')->default(0);

            // The question/prompt
            $table->text('question_text')->nullable();       // Text prompt
            $table->string('question_image_path')->nullable(); // Image prompt
            $table->string('question_audio_path')->nullable(); // Audio prompt
            $table->string('question_emoji')->nullable();      // Emoji prompt

            // Answer options (for quiz, matching, sorting)
            // Each option: { text, image_path, emoji, audio_path, is_correct }
            $table->json('options')->nullable();

            // For matching game: the pair to match with
            $table->string('match_text')->nullable();
            $table->string('match_image_path')->nullable();
            $table->string('match_emoji')->nullable();

            // For fill_lyric: the correct word to fill
            $table->string('correct_answer')->nullable();

            // For spot_difference: coordinates of differences
            $table->json('difference_zones')->nullable(); // [{x, y, width, height}]

            // For rhythm game: beat pattern
            $table->json('beat_pattern')->nullable(); // [1, 0, 1, 1, 0, ...]

            // Hint shown after wrong answer
            $table->text('hint')->nullable();

            // Points for this specific question (overrides game default)
            $table->unsignedInteger('points')->default(10);

            $table->timestamps();

            $table->index(['game_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_questions');
    }
};
