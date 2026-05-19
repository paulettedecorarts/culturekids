<?php

namespace Tests\Unit\Services;

use App\Models\Language;
use App\Models\LanguageActivity;
use App\Models\LanguageActivityWord;
use App\Models\Tribe;
use App\Services\TranslationCoverageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationCoverageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_updates_language_registry_from_activity_words(): void
    {
        $language = Language::create([
            'name' => 'Test Lang',
            'code' => 'tst-UG',
            'translation_coverage' => 0,
            'status' => 'pending',
            'is_active' => true,
        ]);

        $tribe = Tribe::create([
            'name' => 'Tribe',
            'hero_name' => 'Hero',
            'region' => 'Central',
        ]);

        $activity = LanguageActivity::create([
            'tribe_id' => $tribe->id,
            'language_code' => 'tst-UG',
            'title' => 'Words',
            'activity_type' => 'word_trace',
            'difficulty_level' => 'easy',
            'age_min' => 3,
            'age_max' => 5,
            'star_points' => 5,
            'status' => 'published',
        ]);

        LanguageActivityWord::create([
            'language_activity_id' => $activity->id,
            'word' => 'A',
            'translation' => 'One',
            'order_index' => 0,
        ]);

        LanguageActivityWord::create([
            'language_activity_id' => $activity->id,
            'word' => 'B',
            'translation' => '',
            'order_index' => 1,
        ]);

        app(TranslationCoverageService::class)->syncLanguageRegistryWithStatus('tst-UG');

        $language->refresh();
        $this->assertSame(50, $language->translation_coverage);
        $this->assertSame('partial', $language->status);
    }
}
