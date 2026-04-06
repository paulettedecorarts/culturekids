<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('plan')->default('free');
            $table->json('settings')->nullable();
            $table->json('theme')->nullable();
            $table->timestamps();
        });

        Schema::create('tribes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('hero_name');
            $table->string('hero_emoji')->nullable();
            $table->string('hero_icon')->nullable();
            $table->string('greeting')->nullable();
            $table->string('region')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->onDelete('cascade');
            $table->string('type'); // story, song, maze, puzzle, game, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('age_range')->nullable();
            $table->integer('star_points')->default(10);
            $table->json('metadata')->nullable(); // contents of the activity
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('child_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Parent
            $table->string('name');
            $table->date('dob');
            $table->string('age_band'); // simple, guided, advanced, full
            $table->integer('total_stars')->default(0);
            $table->timestamps();
        });

        Schema::create('progress_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->integer('stars_earned');
            $table->timestamp('completed_at')->useCurrent();
            $table->timestamp('synced_at')->nullable();
            $table->string('idempotency_key')->unique()->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_events');
        Schema::dropIfExists('child_profiles');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('tribes');
        Schema::dropIfExists('organisations');
    }
};
