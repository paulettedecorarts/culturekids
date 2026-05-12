<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('culture_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();

            // Culture sub-type
            $table->string('culture_type')->default('clan_story');
            // clan_story | clan_map | clan_design | clan_history | clan_profile

            $table->string('difficulty_level')->default('easy');
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->unsignedInteger('star_points')->default(10);
            $table->string('status')->default('draft');

            // Clan-specific data
            $table->string('clan_name')->nullable();       // e.g. "Gora Clan"
            $table->string('clan_totem')->nullable();      // e.g. "Nile Crocodile"
            $table->string('clan_role')->nullable();       // e.g. "Guardians of the Nile"
            $table->string('clan_emoji')->nullable();      // e.g. "🐊"

            // Rich content
            $table->longText('content')->nullable();       // Main story/history text
            $table->json('content_sections')->nullable();  // [{title, text, image_path}]
            $table->json('quiz_questions')->nullable();    // For clan_story with quiz
            $table->json('map_data')->nullable();          // For clan_map: regions, markers
            $table->json('design_elements')->nullable();   // For clan_design: colors, symbols

            // Media
            $table->string('cover_image_path')->nullable();
            $table->string('map_image_path')->nullable();  // Background map image

            // Cultural context
            $table->text('cultural_note')->nullable();
            $table->string('proverb')->nullable();         // Clan proverb
            $table->string('proverb_translation')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tribe_id', 'status']);
            $table->index(['culture_type', 'status']);
            $table->index(['clan_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('culture_activities');
    }
};
