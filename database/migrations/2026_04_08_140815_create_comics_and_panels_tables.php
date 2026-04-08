<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->foreignId('tribe_id')->constrained('tribes')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->tinyInteger('age_min')->unsigned(); // 2, 3, 4, 5
            $table->tinyInteger('age_max')->unsigned(); // 3, 4, 5, 6
            $table->enum('status', ['draft', 'review', 'published'])->default('draft');
            $table->string('cover_image_path', 500)->nullable();
            $table->string('bundle_path', 500)->nullable(); // S3 path to .ckb bundle
            $table->string('bundle_hash', 64)->nullable(); // For offline sync
            $table->integer('star_points')->default(10);
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();

            $table->index(['tribe_id', 'status']);
            $table->index(['age_min', 'age_max']);
            $table->index('status');
            $table->index('title');
        });

        Schema::create('comic_panels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_id')->constrained('comics')->cascadeOnDelete();
            $table->integer('order_index'); // Panel sequence: 0, 1, 2, 3...
            $table->string('image_path', 500); // S3 or local storage path
            $table->string('audio_url', 500)->nullable(); // Per-panel audio narration
            $table->text('caption')->nullable(); // Panel text/caption
            $table->json('vocab_tags')->nullable(); // Tagged vocabulary words with positions
            $table->json('metadata')->nullable(); // Additional panel metadata
            $table->timestamps();

            $table->index(['comic_id', 'order_index']);
        });

        Schema::create('panel_vocab_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panel_id')->constrained('comic_panels')->cascadeOnDelete();
            $table->string('word'); // English word
            $table->string('translation')->nullable(); // Tribe language translation
            $table->string('phonetic')->nullable(); // Pronunciation guide
            $table->integer('x_position')->nullable(); // Click area X coordinate
            $table->integer('y_position')->nullable(); // Click area Y coordinate
            $table->integer('width')->nullable(); // Click area width
            $table->integer('height')->nullable(); // Click area height
            $table->json('metadata')->nullable(); // Additional vocab metadata
            $table->timestamps();

            $table->index('panel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panel_vocab_tags');
        Schema::dropIfExists('comic_panels');
        Schema::dropIfExists('comics');
    }
};
