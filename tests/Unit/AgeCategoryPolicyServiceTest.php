<?php

namespace Tests\Unit;

use App\Models\AgeProfile;
use App\Models\ChildProfile;
use App\Models\User;
use App\Services\AgeCategoryPolicyService;
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

        $service = new AgeCategoryPolicyService();

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

        $service = new AgeCategoryPolicyService();
        $resolved = $service->resolveForChild($child->fresh());

        $this->assertNotNull($resolved);
        $this->assertSame($category->id, $resolved->id);
    }

    public function test_it_builds_ui_policy_payload(): void
    {
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

        $service = new AgeCategoryPolicyService();
        $payload = $service->enrichUiPolicyPayload($category);

        $this->assertSame('band_3_4', $payload['age_profile']['key']);
        $this->assertSame(['Large tiles'], $payload['ui_features']);
        $this->assertSame(['stories', 'songs'], $payload['content_access_rules']['modules']);
    }
}
