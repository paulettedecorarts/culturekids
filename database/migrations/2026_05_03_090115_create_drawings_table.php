<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drawings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('drawing_type')->default('coloring'); // coloring, hero_drawing, design_tool, free_draw
            $table->string('difficulty_level')->default('easy'); // easy, medium, hard
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->unsignedInteger('star_points')->default(10);
            $table->string('status')->default('draft'); // draft, published, archived
            $table->string('template_path')->nullable(); // Path to template image
            $table->string('preview_path')->nullable(); // Path to preview image
            $table->json('tools_config')->nullable(); // Available tools configuration
            $table->json('color_palette')->nullable(); // Available colors
            $table->json('materials')->nullable(); // Required materials (crayons, paper, etc.)
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();

            $table->index(['tribe_id', 'status']);
            $table->index(['drawing_type', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drawings');
    }
};