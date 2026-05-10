<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mazes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();

            // Maze sub-type
            $table->string('maze_type')->default('standard');
            // standard | timed | collect_items | visibility | reverse | circular

            $table->string('difficulty_level')->default('easy'); // easy, medium, hard, expert, master
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->unsignedInteger('star_points')->default(10);
            $table->string('status')->default('draft');

            // Maze grid — stored as 2D array of 0 (path) and 1 (wall)
            // e.g. [[1,1,1],[1,0,1],[1,0,1],[1,1,1]]
            $table->json('grid')->nullable();
            $table->unsignedInteger('grid_rows')->default(10);
            $table->unsignedInteger('grid_cols')->default(10);

            // Start and end positions
            $table->json('start_position')->nullable(); // {row: 0, col: 1}
            $table->json('end_position')->nullable();   // {row: 9, col: 8}

            // Collectible items placed on the maze path
            $table->json('collectibles')->nullable(); // [{row, col, emoji, label, required}]

            // Timed maze settings
            $table->unsignedInteger('time_limit_seconds')->nullable();

            // Visibility maze — radius of visible area around player
            $table->unsignedInteger('visibility_radius')->nullable(); // in grid cells

            // Theme / background image
            $table->string('background_image_path')->nullable();
            $table->string('cover_image_path')->nullable();

            // Cultural context
            $table->text('cultural_note')->nullable();
            $table->string('hero_character')->nullable(); // e.g. "Gipir", "Labongo"

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tribe_id', 'status']);
            $table->index(['maze_type', 'difficulty_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mazes');
    }
};
