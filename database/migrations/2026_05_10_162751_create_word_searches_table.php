<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('difficulty_level')->default('easy'); // easy, medium, hard, expert
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->unsignedInteger('star_points')->default(10);
            $table->string('status')->default('draft');

            // Grid configuration
            $table->unsignedInteger('grid_size')->default(10); // NxN grid
            $table->boolean('allow_diagonal')->default(false);
            $table->boolean('allow_reverse')->default(false);

            // Words to find (array of {word, translation, hint})
            $table->json('words')->nullable();

            // The generated letter grid (2D array)
            $table->json('grid')->nullable();

            // Word positions in the grid (for answer checking)
            // [{word, start_row, start_col, direction, cells:[{row,col}]}]
            $table->json('word_positions')->nullable();

            // Cultural context
            $table->text('cultural_note')->nullable();
            $table->string('language_code')->nullable(); // if words are in a specific language

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tribe_id', 'status']);
            $table->index(['difficulty_level', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_searches');
    }
};
