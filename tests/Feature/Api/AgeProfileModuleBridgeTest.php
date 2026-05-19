<?php

namespace Tests\Feature\Api;

use App\Models\AgeProfile;
use App\Models\Module;
use App\Models\Organisation;
use App\Models\User;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AgeProfileModuleBridgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
    }

    public function test_age_profiles_include_effective_modules_for_authenticated_org_user(): void
    {
        $org = Organisation::create([
            'name' => 'Age Bridge School',
            'code' => 'age-bridge',
            'plan' => 'school',
            'status' => 'active',
        ]);

        $this->disableModule($org, 'stories');

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');

        Sanctum::actingAs($user);

        $profile = AgeProfile::query()->where('key', 'young_thinkers')->firstOrFail();

        $response = $this->getJson('/api/v1/age-profiles')->assertOk();

        $row = collect($response->json('age_profiles'))->firstWhere('key', $profile->key);
        $this->assertNotNull($row);

        $rules = $row['content_access_rules'];
        $this->assertContains('stories', $rules['modules']);
        $this->assertNotContains('stories', $rules['effective_modules']);
        $this->assertContains('puzzle', $rules['effective_modules']);
        $this->assertContains('puzzles', $rules['effective_organisation_module_keys']);
        $this->assertNotContains('stories', $rules['effective_organisation_module_keys']);
        $this->assertContains('puzzle', $rules['effective_activity_types']);
    }

    public function test_b2c_user_sees_all_age_profile_modules_as_effective(): void
    {
        $user = User::factory()->create(['organisation_id' => null]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');

        Sanctum::actingAs($user);

        $profile = AgeProfile::query()->where('key', 'early_explorers')->firstOrFail();
        $modules = $profile->content_access_rules['modules'] ?? [];

        $row = collect($this->getJson('/api/v1/age-profiles')->json('age_profiles'))
            ->firstWhere('key', 'early_explorers');

        $this->assertEqualsCanonicalizing($modules, $row['content_access_rules']['effective_modules']);
    }

    private function disableModule(Organisation $org, string $moduleKey): void
    {
        $module = Module::query()->where('key', $moduleKey)->firstOrFail();
        $org->modules()->sync([
            $module->id => ['is_enabled' => false],
        ]);
    }
}
