<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();

            $table->string('name');                        // e.g. "Gora Clan"
            $table->string('totem')->nullable();           // e.g. "Nile Crocodile"
            $table->string('totem_emoji')->nullable();     // e.g. "🐊"
            $table->string('role')->nullable();            // e.g. "Guardians of the Nile"
            $table->string('region')->nullable();          // e.g. "Northwestern Uganda"
            $table->text('description')->nullable();       // Brief clan description
            $table->text('history')->nullable();           // Longer historical narrative
            $table->string('proverb')->nullable();         // Clan proverb
            $table->string('proverb_translation')->nullable();
            $table->string('color')->nullable();           // Clan colour (hex)
            $table->string('cover_image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(100);

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tribe_id', 'is_active']);
            $table->index(['tribe_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clans');
    }
};
