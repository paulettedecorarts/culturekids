<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            
            // Color palette
            $table->json('colors'); // primary, secondary, accent, background, text, etc.
            
            // Typography
            $table->json('typography')->nullable(); // font families, sizes, weights
            
            // Spacing & Layout
            $table->json('spacing')->nullable(); // padding, margins, gaps
            
            // Border radius
            $table->json('borders')->nullable(); // radius values
            
            // Additional metadata
            $table->json('metadata')->nullable();
            
            $table->string('preview_image')->nullable();
            $table->timestamps();
            
            // Index for org-specific themes
            $table->index(['org_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
