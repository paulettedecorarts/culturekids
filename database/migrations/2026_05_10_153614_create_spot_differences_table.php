<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spot_differences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('difficulty_level')->default('easy'); // easy, medium, hard
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->unsignedInteger('star_points')->default(10);
            $table->string('status')->default('draft');

            // The two images to compare
            $table->string('image_a_path')->nullable(); // Original image
            $table->string('image_b_path')->nullable(); // Modified image (with differences)

            // Game settings
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->unsignedInteger('total_differences')->default(5); // How many differences exist

            // Cultural context
            $table->text('cultural_note')->nullable();
            $table->string('scene_name')->nullable(); // e.g. "Alur Village Scene"

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tribe_id', 'status']);
            $table->index(['difficulty_level', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spot_differences');
    }
};
