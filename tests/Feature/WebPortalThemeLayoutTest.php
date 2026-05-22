<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\Theme;
use App\Models\User;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebPortalThemeLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
    }

    public function test_cms_layout_injects_organisation_theme_css_variables(): void
    {
        $org = Organisation::create([
            'name' => 'Layout Theme School',
            'code' => 'layout-theme-school',
            'plan' => 'school',
            'status' => 'active',
        ]);

        Theme::create([
            'org_id' => $org->id,
            'name' => 'Purple Brand',
            'slug' => 'purple_brand',
            'is_default' => true,
            'is_active' => true,
            'colors' => ['primary' => '#663399'],
        ]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'org_admin', 'guard_name' => 'web']);
        $user->assignRole('org_admin');

        $response = $this->actingAs($user)->get(route('cms.admin.dashboard'));

        $response->assertOk();
        $response->assertSee('id="portal-org-theme"', false);
        $response->assertSee('--clay-red: #663399', false);
    }

    public function test_disabled_theme_engine_uses_platform_default_colours(): void
    {
        $org = Organisation::create([
            'name' => 'No Theme Engine',
            'code' => 'no-theme-engine',
            'plan' => 'school',
            'status' => 'active',
        ]);

        Theme::create([
            'org_id' => $org->id,
            'name' => 'Hidden Brand',
            'slug' => 'hidden_brand',
            'is_default' => true,
            'is_active' => true,
            'colors' => ['primary' => '#112233'],
        ]);

        $module = \App\Models\Module::query()->where('key', 'theme_engine')->firstOrFail();
        $org->modules()->sync([$module->id => ['is_enabled' => false]]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'org_admin', 'guard_name' => 'web']);
        $user->assignRole('org_admin');

        $response = $this->actingAs($user)->get(route('cms.admin.dashboard'));

        $response->assertOk();
        $response->assertSee('--clay-red: #C44B2B', false);
        $response->assertDontSee('--clay-red: #112233', false);
    }
}
