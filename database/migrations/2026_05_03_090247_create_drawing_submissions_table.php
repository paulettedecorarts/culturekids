<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drawing_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drawing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('artwork_path'); // Path to saved artwork image
            $table->string('thumbnail_path')->nullable(); // Path to thumbnail
            $table->boolean('completed')->default(false);
            $table->unsignedInteger('stars_earned')->default(0);
            $table->unsignedInteger('time_spent_seconds')->default(0); // Time spent drawing
            $table->json('tools_used')->nullable(); // Which tools were used
            $table->json('drawing_data')->nullable(); // Canvas data for resuming
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['drawing_id', 'user_id']);
            $table->index(['user_id', 'completed']);
            $table->index(['completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drawing_submissions');
    }
};