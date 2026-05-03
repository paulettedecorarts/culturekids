<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add interactive activity fields to songs table
        Schema::table('songs', function (Blueprint $table) {
            $table->string('activity_type')->default('karaoke')->after('song_type');
            $table->string('difficulty_level')->nullable()->after('activity_type');
            $table->boolean('has_karaoke_timing')->default(false)->after('difficulty_level');
            $table->boolean('has_fill_blanks')->default(false)->after('has_karaoke_timing');
            $table->json('interaction_config')->nullable()->after('has_fill_blanks');
        });

        // Create song lyric segments table for karaoke timing
        Schema::create('song_lyric_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->text('segment_text');
            $table->decimal('start_time', 8, 3); // seconds with millisecond precision
            $table->decimal('end_time', 8, 3);
            $table->unsignedInteger('order_index');
            $table->string('segment_type')->default('verse'); // verse, chorus, bridge, etc.
            $table->boolean('is_fill_blank')->default(false);
            $table->string('blank_answer')->nullable(); // for fill-the-lyric games
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['song_id', 'order_index']);
            $table->index(['song_id', 'start_time']);
        });

        // Create song activities table to track user interactions
        Schema::create('song_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('activity_type'); // karaoke, fill_blanks, lullaby, etc.
            $table->json('completion_data')->nullable(); // scores, timing, answers
            $table->unsignedInteger('stars_earned')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['song_id', 'user_id']);
            $table->index(['user_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_activities');
        Schema::dropIfExists('song_lyric_segments');
        
        Schema::table('songs', function (Blueprint $table) {
            $table->dropColumn([
                'activity_type',
                'difficulty_level', 
                'has_karaoke_timing',
                'has_fill_blanks',
                'interaction_config'
            ]);
        });
    }
};