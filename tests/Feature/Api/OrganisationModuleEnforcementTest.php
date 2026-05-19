<?php

namespace Tests\Feature\Api;

use App\Models\Activity;
use App\Models\Comic;
use App\Models\Module;
use App\Models\Organisation;
use App\Models\Theme;
use App\Models\Tribe;
use App\Models\User;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganisationModuleEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
    }

    public function test_activities_index_excludes_puzzles_when_module_disabled(): void
    {
        $org = $this->createOrg();
        $this->disableModuleForOrg($org, 'puzzles');

        $tribe = Tribe::create([
            'name' => 'Test Tribe',
            'hero_name' => 'Hero',
            'region' => 'Central',
        ]);

        Activity::create([
            'tribe_id' => $tribe->id,
            'type' => 'puzzle',
            'title' => 'Shape Puzzle',
            'is_published' => true,
            'star_points' => 5,
        ]);

        Activity::create([
            'tribe_id' => $tribe->id,
            'type' => 'flashcard',
            'title' => 'Hello',
            'is_published' => true,
            'star_points' => 5,
        ]);

        $user = $this->createOrgParent($org);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/activities')->assertOk();

        $types = collect($response->json())->pluck('type');
        $this->assertNotContains('puzzle', $types);
        $this->assertContains('flashcard', $types);
    }

    public function test_activities_filtered_by_type_returns_403_when_module_disabled(): void
    {
        $org = $this->createOrg();
        $this->disableModuleForOrg($org, 'puzzles');

        $user = $this->createOrgParent($org);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/activities?type=puzzle')->assertForbidden();
    }

    public function test_offline_tribe_bundle_omits_stories_when_stories_module_disabled(): void
    {
        $org = $this->createOrg();
        $this->disableModuleForOrg($org, 'stories');

        $tribe = Tribe::create([
            'name' => 'Bundle Tribe',
            'hero_name' => 'Hero',
            'region' => 'East',
        ]);

        Comic::create([
            'tribe_id' => $tribe->id,
            'title' => 'Hidden Story',
            'status' => 'published',
            'age_min' => 2,
            'age_max' => 6,
            'star_points' => 10,
        ]);

        $user = $this->createOrgParent($org);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/offline/tribes/{$tribe->id}/bundle")
            ->assertOk()
            ->assertJsonPath('stats.comics_count', 0)
            ->assertJsonCount(0, 'comics');
    }

    public function test_offline_comic_download_requires_stories_module(): void
    {
        $org = $this->createOrg();
        $this->disableModuleForOrg($org, 'stories');

        $tribe = Tribe::create([
            'name' => 'DL Tribe',
            'hero_name' => 'Hero',
            'region' => 'West',
        ]);

        $comic = Comic::create([
            'tribe_id' => $tribe->id,
            'title' => 'DL Story',
            'status' => 'published',
            'age_min' => 2,
            'age_max' => 6,
            'star_points' => 10,
        ]);

        $user = $this->createOrgParent($org);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/offline/comics/{$comic->id}/download")->assertForbidden();
    }

    public function test_theme_endpoint_returns_platform_default_when_theme_engine_disabled(): void
    {
        $org = Organisation::create([
            'name' => 'Branded School',
            'code' => 'branded-school',
            'plan' => 'school',
            'status' => 'active',
        ]);

        Theme::create([
            'org_id' => $org->id,
            'name' => 'School Brand',
            'slug' => 'school_brand',
            'is_default' => true,
            'is_active' => true,
            'colors' => ['primary' => '#112233'],
        ]);

        $this->disableModuleForOrg($org, 'theme_engine');

        $user = $this->createOrgParent($org);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/organisation/theme')
            ->assertOk()
            ->assertJsonPath('theme_engine_enabled', false)
            ->assertJsonPath('theme.source', 'platform_default')
            ->assertJsonPath('theme.colors.primary', '#C44B2B');
    }

    public function test_reading_progress_requires_stories_module(): void
    {
        $org = $this->createOrg();
        $this->disableModuleForOrg($org, 'stories');

        $user = $this->createOrgParent($org);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/reading-progress')->assertForbidden();
    }

    private function createOrg(): Organisation
    {
        return Organisation::create([
            'name' => 'Module Test School',
            'code' => 'module-test-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);
    }

    private function createOrgParent(Organisation $org): User
    {
        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');

        return $user;
    }

    private function disableModuleForOrg(Organisation $org, string $moduleKey): void
    {
        $module = Module::query()->where('key', $moduleKey)->firstOrFail();
        $org->modules()->sync([
            $module->id => ['is_enabled' => false],
        ]);
    }
}
