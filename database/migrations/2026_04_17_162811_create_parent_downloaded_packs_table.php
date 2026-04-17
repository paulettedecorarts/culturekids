<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parent_downloaded_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tribe_id')->constrained()->onDelete('cascade');
            $table->timestamp('downloaded_at');
            $table->timestamps();
            
            // Ensure a parent can only have one record per tribe
            $table->unique(['user_id', 'tribe_id']);
            
            $table->index('user_id');
            $table->index('tribe_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_downloaded_packs');
    }
};
