<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'tribe_directory', 'comics'
            $table->string('name'); // Display name
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Emoji or icon identifier
            $table->boolean('is_enabled')->default(true); // Global toggle
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable(); // Additional config
            $table->timestamps();
        });

        // Pivot table for organization-specific module access
        Schema::create('module_organisation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->foreignId('organisation_id')->constrained('organisations')->onDelete('cascade');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            
            $table->unique(['module_id', 'organisation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_organisation');
        Schema::dropIfExists('modules');
    }
};
