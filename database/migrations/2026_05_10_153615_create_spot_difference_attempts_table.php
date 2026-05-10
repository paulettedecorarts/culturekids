<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spot_difference_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spot_difference_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->boolean('completed')->default(false);
            $table->unsignedInteger('stars_earned')->default(0);
            $table->unsignedInteger('differences_found')->default(0);
            $table->unsignedInteger('wrong_taps')->default(0);
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->unsignedInteger('attempts_count')->default(1);
            $table->json('found_zone_ids')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['spot_difference_id', 'user_id']);
            $table->index(['user_id', 'completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spot_difference_attempts');
    }
};
