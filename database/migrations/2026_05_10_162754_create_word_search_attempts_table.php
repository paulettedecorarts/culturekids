<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_search_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_search_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->boolean('completed')->default(false);
            $table->unsignedInteger('stars_earned')->default(0);
            $table->unsignedInteger('words_found')->default(0);
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->unsignedInteger('attempts_count')->default(1);
            $table->json('found_words')->nullable(); // list of found word strings

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['word_search_id', 'user_id']);
            $table->index(['user_id', 'completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_search_attempts');
    }
};
