<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();

            // Game type determines the mechanic/engine used
            $table->string('game_type')->default('matching');
            // matching | quiz | rhythm | fill_lyric | spot_difference | memory | sorting

            $table->string('difficulty_level')->default('easy'); // easy, medium, hard
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->unsignedInteger('star_points')->default(10);
            $table->string('status')->default('draft'); // draft, published, archived

            // Game settings
            $table->unsignedInteger('time_limit_seconds')->nullable(); // null = no limit
            $table->unsignedInteger('lives')->default(3);              // attempts before game over
            $table->boolean('shuffle_questions')->default(true);
            $table->unsignedInteger('questions_per_round')->default(10);

            // Media
            $table->string('cover_image_path')->nullable();
            $table->string('background_music_path')->nullable();

            // Cultural context
            $table->text('cultural_note')->nullable();
            $table->string('language_code')->nullable(); // if language-specific

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tribe_id', 'status']);
            $table->index(['game_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
