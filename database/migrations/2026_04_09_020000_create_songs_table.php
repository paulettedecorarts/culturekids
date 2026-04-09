<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('language')->nullable();
            $table->string('song_type')->default('traditional_song');
            $table->longText('lyrics')->nullable();
            $table->string('audio_path')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->unsignedInteger('star_points')->default(10);
            $table->string('status')->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tribe_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        if (Schema::hasTable('activities')) {
            $songs = DB::table('activities')
                ->where('type', 'song')
                ->orderBy('id')
                ->get();

            foreach ($songs as $activity) {
                $metadata = [];
                if (is_string($activity->metadata)) {
                    $decoded = json_decode($activity->metadata, true);
                    $metadata = is_array($decoded) ? $decoded : [];
                } elseif (is_array($activity->metadata)) {
                    $metadata = $activity->metadata;
                }

                preg_match('/(\d+)\D+(\d+)/', (string) $activity->age_range, $matches);
                $ageMin = isset($matches[1]) ? (int) $matches[1] : null;
                $ageMax = isset($matches[2]) ? (int) $matches[2] : null;

                DB::table('songs')->insert([
                    'org_id' => null,
                    'tribe_id' => $activity->tribe_id,
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'language' => $metadata['language'] ?? null,
                    'song_type' => $metadata['song_type'] ?? 'traditional_song',
                    'lyrics' => $metadata['lyrics'] ?? null,
                    'audio_path' => $metadata['audio_path'] ?? $metadata['audio_url'] ?? $metadata['track_url'] ?? null,
                    'cover_image_path' => $metadata['cover_image_path'] ?? $metadata['cover'] ?? null,
                    'duration_seconds' => isset($metadata['duration_seconds']) ? (int) $metadata['duration_seconds'] : null,
                    'age_min' => $ageMin,
                    'age_max' => $ageMax,
                    'star_points' => (int) ($activity->star_points ?? 10),
                    'status' => $activity->is_published ? 'published' : 'draft',
                    'metadata' => json_encode($metadata),
                    'created_at' => $activity->created_at ?? now(),
                    'updated_at' => $activity->updated_at ?? now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
