<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('language_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->string('language_code'); // e.g. lug-UG, ach-UG
            $table->string('title');
            $table->text('description')->nullable();

            // Activity sub-type
            $table->string('activity_type')->default('word_trace');
            // word_trace | audio_match | speak_back | proverb_jumble | sentence_builder

            $table->string('difficulty_level')->default('easy'); // easy, medium, hard
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->unsignedInteger('star_points')->default(10);
            $table->string('status')->default('draft'); // draft, published, archived

            // For proverb_jumble / sentence_builder
            $table->text('full_sentence')->nullable();       // The complete correct sentence
            $table->text('sentence_translation')->nullable(); // English translation

            // For audio_match
            $table->string('audio_path')->nullable(); // Audio file to play

            // Cultural context
            $table->text('cultural_note')->nullable(); // Why this word/phrase matters

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tribe_id', 'status']);
            $table->index(['activity_type', 'status']);
            $table->index(['language_code', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('language_activities');
    }
};
