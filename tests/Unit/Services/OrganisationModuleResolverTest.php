<?php

namespace Tests\Unit\Services;

use App\Models\Module;
use App\Models\Organisation;
use App\Models\OrganisationContentDecision;
use App\Models\User;
use App\Services\OrganisationModuleResolver;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganisationModuleResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
    }

    public function test_seeder_installs_fifteen_canonical_modules(): void
    {
        $this->assertSame(15, Module::query()->count());
        $this->assertDatabaseMissing('modules', ['key' => 'comics']);
        $this->assertDatabaseHas('modules', ['key' => 'stories']);
    }

    public function test_all_twelve_content_types_map_to_a_module_key(): void
    {
        foreach (OrganisationContentDecision::ALL_TYPES as $contentType) {
            $this->assertNotNull(
                OrganisationModuleResolver::moduleKeyForContentType($contentType),
                "Missing module mapping for content type [{$contentType}]"
            );
        }
    }

    public function test_org_without_pivot_row_has_module_enabled_by_default(): void
    {
        $org = Organisation::create([
            'name' => 'Default Modules Org',
            'code' => 'default-modules',
            'plan' => 'school',
            'status' => 'active',
        ]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        $resolver = app(OrganisationModuleResolver::class);

        $this->assertTrue($resolver->isEnabledForUser($user, 'stories'));
        $this->assertTrue($resolver->isContentTypeAllowed($user, OrganisationContentDecision::TYPE_STORY));
    }

    public function test_globally_disabled_module_is_off_for_everyone(): void
    {
        Module::query()->where('key', 'songs')->update(['is_enabled' => false]);

        $user = User::factory()->create(['organisation_id' => null]);
        $resolver = app(OrganisationModuleResolver::class);

        $this->assertFalse($resolver->isEnabledForUser($user, 'songs'));
        $this->assertFalse($resolver->isContentTypeAllowed($user, OrganisationContentDecision::TYPE_SONG));
    }

    public function test_activity_types_map_to_expected_module_keys(): void
    {
        $map = OrganisationModuleResolver::activityTypeToModuleKey();

        $this->assertSame('stories', $map['story']);
        $this->assertSame('puzzles', $map['puzzle']);
        $this->assertSame('language_activities', $map['vocab_pack']);
        $this->assertSame('culture_activities', $map['culture']);
    }

    public function test_effective_age_profile_modules_respect_organisation_toggles(): void
    {
        $org = Organisation::create([
            'name' => 'Bridge Org',
            'code' => 'bridge-org',
            'plan' => 'school',
            'status' => 'active',
        ]);

        $stories = Module::query()->where('key', 'stories')->firstOrFail();
        $org->modules()->sync([$stories->id => ['is_enabled' => false]]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        $resolver = app(OrganisationModuleResolver::class);

        $ageModules = ['stories', 'songs', 'puzzle', 'flashcard'];

        $this->assertEqualsCanonicalizing(
            ['songs', 'puzzle', 'flashcard'],
            $resolver->effectiveAgeProfileModulesForUser($user, $ageModules)
        );

        $this->assertEqualsCanonicalizing(
            ['song', 'puzzle', 'flashcard'],
            $resolver->effectiveActivityTypesForAgeProfileModules($user, $ageModules)
        );

        $this->assertFalse($resolver->isAgeProfileModuleAllowedForUser($user, 'stories'));
        $this->assertTrue($resolver->isAgeProfileModuleAllowedForUser($user, 'puzzle'));
    }
}
