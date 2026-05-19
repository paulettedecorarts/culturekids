<?php

namespace Tests\Feature\Api;

use App\Models\Organisation;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganisationThemeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_organisation_theme_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/organisation/theme')->assertUnauthorized();
    }

    public function test_user_without_organisation_receives_platform_default_theme(): void
    {
        $user = $this->createParentUser();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/organisation/theme')
            ->assertOk()
            ->assertJsonPath('theme.source', 'platform_default')
            ->assertJsonPath('theme.organisation_id', null)
            ->assertJsonPath('theme.colors.primary', '#C44B2B');
    }

    public function test_organisation_user_receives_default_theme_record(): void
    {
        $org = Organisation::create([
            'name' => 'Sunrise Primary',
            'code' => 'sunrise-primary',
            'plan' => 'school',
            'status' => 'active',
        ]);

        Theme::create([
            'org_id' => $org->id,
            'name' => 'Sunrise Brand',
            'slug' => 'sunrise_brand',
            'is_default' => true,
            'is_active' => true,
            'colors' => [
                'primary' => '#2E4D8A',
                'secondary' => '#4A72C4',
            ],
        ]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $user->assignRole('teacher');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/organisation/theme')
            ->assertOk()
            ->assertJsonPath('theme.source', 'organisation_theme')
            ->assertJsonPath('theme.organisation_id', $org->id)
            ->assertJsonPath('theme.name', 'Sunrise Brand')
            ->assertJsonPath('theme.colors.primary', '#2E4D8A')
            ->assertJsonPath('theme.colors.accent', '#D4A017');
    }

    public function test_organisation_json_theme_overrides_are_merged(): void
    {
        $org = Organisation::create([
            'name' => 'Lakeview School',
            'code' => 'lakeview',
            'plan' => 'school',
            'status' => 'active',
            'theme' => [
                'colors' => [
                    'primary' => '#111111',
                ],
                'name' => 'Lakeview Custom',
            ],
        ]);

        Theme::create([
            'org_id' => $org->id,
            'name' => 'Lakeview Base',
            'slug' => 'lakeview_base',
            'is_default' => true,
            'is_active' => true,
            'colors' => ['primary' => '#2E4D8A'],
        ]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/organisation/theme')
            ->assertOk()
            ->assertJsonPath('theme.source', 'organisation_override')
            ->assertJsonPath('theme.name', 'Lakeview Custom')
            ->assertJsonPath('theme.colors.primary', '#111111');
    }

    private function createParentUser(): User
    {
        $user = User::factory()->create(['organisation_id' => null]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');

        return $user;
    }
}
