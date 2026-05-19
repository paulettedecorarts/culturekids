<?php

namespace Tests\Unit;

use App\Models\AgeProfile;
use App\Models\ChildProfile;
use App\Models\User;
use App\Services\AgeCategoryPolicyService;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgeCategoryPolicyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_the_correct_category_for_age(): void
    {
        AgeProfile::create([
            'name' => 'Band 10-11',
            'key' => 'band_10_11',
            'min_age' => 10,
            'max_age' => 11,
        ]);

        AgeProfile::create([
            'name' => 'Band 12-13',
            'key' => 'band_12_13',
            'min_age' => 12,
            'max_age' => 13,
        ]);

        $service = app(AgeCategoryPolicyService::class);

        $resolved = $service->resolveForAge(12);

        $this->assertNotNull($resolved);
        $this->assertSame('band_12_13', $resolved->key);
    }

    public function test_it_prefers_assigned_category_on_child_profile(): void
    {
        $category = AgeProfile::create([
            'name' => 'Assigned Band',
            'key' => 'assigned_band',
            'min_age' => 2,
            'max_age' => 6,
        ]);

        $user = User::factory()->create();
        $child = ChildProfile::create([
            'user_id' => $user->id,
            'name' => 'Amina',
            'dob' => now()->subYears(4)->toDateString(),
            'age_band' => 'guided',
            'age_profile_id' => $category->id,
        ]);

        $service = app(AgeCategoryPolicyService::class);
        $resolved = $service->resolveForChild($child->fresh());

        $this->assertNotNull($resolved);
        $this->assertSame($category->id, $resolved->id);
    }

    public function test_it_builds_ui_policy_payload(): void
    {
        $this->seed(ModuleSeeder::class);

        $category = AgeProfile::create([
            'name' => 'Band 3-4',
            'key' => 'band_3_4',
            'min_age' => 3,
            'max_age' => 4,
            'ui_scale' => 'large',
            'touch_target_px' => 64,
            'reading_level' => 'short_labels',
            'activity_complexity' => 'two_choice',
            'is_audio_first' => true,
            'ui_features' => ['Large tiles'],
            'content_access_rules' => ['modules' => ['stories', 'songs']],
        ]);

        $service = app(AgeCategoryPolicyService::class);
        $payload = $service->enrichUiPolicyPayload($category);

        $this->assertSame('band_3_4', $payload['age_profile']['key']);
        $this->assertSame(['Large tiles'], $payload['ui_features']);
        $this->assertSame(['stories', 'songs'], $payload['content_access_rules']['modules']);
        $this->assertSame(['stories', 'songs'], $payload['content_access_rules']['effective_modules']);
    }

    public function test_enrich_ui_policy_payload_respects_disabled_organisation_modules(): void
    {
        $this->seed(\Database\Seeders\ModuleSeeder::class);

        $category = AgeProfile::create([
            'name' => 'Band 4-5',
            'key' => 'band_4_5',
            'min_age' => 4,
            'max_age' => 5,
            'content_access_rules' => ['modules' => ['stories', 'songs', 'puzzle']],
        ]);

        $org = \App\Models\Organisation::create([
            'name' => 'Bridge School',
            'code' => 'bridge-school',
            'plan' => 'school',
            'status' => 'active',
        ]);

        $storiesModule = \App\Models\Module::query()->where('key', 'stories')->firstOrFail();
        $org->modules()->sync([$storiesModule->id => ['is_enabled' => false]]);

        $user = User::factory()->create(['organisation_id' => $org->id]);

        $payload = app(AgeCategoryPolicyService::class)->enrichUiPolicyPayload($category, $user);

        $this->assertSame(['stories', 'songs', 'puzzle'], $payload['content_access_rules']['modules']);
        $this->assertEqualsCanonicalizing(['songs', 'puzzle'], $payload['content_access_rules']['effective_modules']);
        $this->assertNotContains('stories', $payload['content_access_rules']['effective_modules']);
        $this->assertEqualsCanonicalizing(['songs', 'puzzles'], $payload['content_access_rules']['effective_organisation_module_keys']);
    }
}
