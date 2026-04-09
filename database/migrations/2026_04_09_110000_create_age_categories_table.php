<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('age_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->unsignedTinyInteger('min_age');
            $table->unsignedTinyInteger('max_age')->nullable();
            $table->string('icon_emoji')->nullable();
            $table->string('color')->nullable();
            $table->string('ui_scale')->default('standard');
            $table->unsignedSmallInteger('touch_target_px')->default(52);
            $table->string('reading_level')->default('short_labels');
            $table->string('activity_complexity')->default('guided');
            $table->json('content_access_rules')->nullable();
            $table->json('ui_features')->nullable();
            $table->boolean('is_audio_first')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('child_profiles', function (Blueprint $table) {
            $table->foreignId('age_profile_id')->nullable()->after('age_band')->constrained('age_profiles')->nullOnDelete();
        });

        $defaults = [
            [
                'name' => 'Early Explorers',
                'key' => 'early_explorers',
                'min_age' => 2,
                'max_age' => 3,
                'icon_emoji' => '🌱',
                'color' => '#C44B2B',
                'ui_scale' => 'giant',
                'touch_target_px' => 80,
                'reading_level' => 'audio_only',
                'activity_complexity' => 'single_action',
                'content_access_rules' => json_encode([
                    'modules' => ['stories', 'songs'],
                    'notes' => 'Audio-first. No reading required.',
                ]),
                'ui_features' => json_encode([
                    'Giant icon tiles',
                    'Audio-first navigation',
                    'Instant audio feedback',
                ]),
                'is_audio_first' => true,
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Curious Learners',
                'key' => 'curious_learners',
                'min_age' => 3,
                'max_age' => 4,
                'icon_emoji' => '🌿',
                'color' => '#D4A017',
                'ui_scale' => 'large',
                'touch_target_px' => 64,
                'reading_level' => 'short_labels',
                'activity_complexity' => 'two_choice',
                'content_access_rules' => json_encode([
                    'modules' => ['stories', 'songs', 'puzzle'],
                    'notes' => 'Simple two-choice interactions.',
                ]),
                'ui_features' => json_encode([
                    'Large tiles',
                    'Guided tap hints',
                    'Celebratory animations',
                ]),
                'is_audio_first' => true,
                'is_active' => true,
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Young Thinkers',
                'key' => 'young_thinkers',
                'min_age' => 4,
                'max_age' => 5,
                'icon_emoji' => '🌳',
                'color' => '#4A7C59',
                'ui_scale' => 'standard',
                'touch_target_px' => 52,
                'reading_level' => 'short_words',
                'activity_complexity' => 'multi_choice',
                'content_access_rules' => json_encode([
                    'modules' => ['stories', 'songs', 'puzzle', 'vocab_pack', 'flashcard'],
                    'notes' => 'Drag-and-drop and short reading unlocked.',
                ]),
                'ui_features' => json_encode([
                    'Progress visible',
                    'Drag-and-drop unlocked',
                    'Short sentence captions',
                ]),
                'is_audio_first' => false,
                'is_active' => true,
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Confident Explorers',
                'key' => 'confident_explorers',
                'min_age' => 5,
                'max_age' => 6,
                'icon_emoji' => '🌟',
                'color' => '#E8872A',
                'ui_scale' => 'compact',
                'touch_target_px' => 44,
                'reading_level' => 'sentences',
                'activity_complexity' => 'open_ended',
                'content_access_rules' => json_encode([
                    'modules' => ['stories', 'songs', 'puzzle', 'vocab_pack', 'flashcard', 'worksheet', 'game'],
                    'notes' => 'Full content types and sentence reading.',
                ]),
                'ui_features' => json_encode([
                    'Compact layout',
                    'Multiple paragraph captions',
                    'Cultural notes unlocked',
                ]),
                'is_audio_first' => false,
                'is_active' => true,
                'sort_order' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('age_profiles')->insert($defaults);
    }

    public function down(): void
    {
        Schema::table('child_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('age_profile_id');
        });

        Schema::dropIfExists('age_profiles');
    }
};
