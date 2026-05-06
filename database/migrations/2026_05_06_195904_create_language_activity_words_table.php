<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('language_activity_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_activity_id')->constrained()->cascadeOnDelete();

            $table->string('word');                    // Native word e.g. "PIJ"
            $table->string('translation');             // English translation e.g. "Water"
            $table->string('phonetic')->nullable();    // Pronunciation guide e.g. "pee-j"
            $table->string('emoji')->nullable();       // Visual hint e.g. 💧
            $table->string('image_path')->nullable();  // Optional image for audio_match
            $table->string('audio_path')->nullable();  // Word-level audio pronunciation

            // For word_trace
            $table->json('trace_path')->nullable();    // SVG/canvas path data for tracing

            // For audio_match - this word is one of the options shown
            $table->boolean('is_correct_answer')->default(false);

            // For proverb_jumble / sentence_builder - word position in sentence
            $table->unsignedInteger('order_index')->default(0);

            // For sentence_builder - some words can be pre-placed (not draggable)
            $table->boolean('is_fixed')->default(false);

            $table->timestamps();

            $table->index(['language_activity_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('language_activity_words');
    }
};
