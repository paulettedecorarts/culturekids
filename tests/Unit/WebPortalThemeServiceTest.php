<?php

namespace Tests\Unit;

use App\Models\Organisation;
use App\Models\Theme;
use App\Models\User;
use App\Services\WebPortalThemeService;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebPortalThemeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
    }

    public function test_maps_resolved_colours_to_legacy_css_variables(): void
    {
        $service = app(WebPortalThemeService::class);

        $vars = $service->toCssVariables([
            'primary' => '#2E4D8A',
            'secondary' => '#4A72C4',
            'accent' => '#D4A017',
            'success' => '#4A7C59',
            'danger' => '#9A3218',
            'background' => '#FAF6F0',
            'surface' => '#FFFFFF',
            'text_primary' => '#1A1208',
            'text_secondary' => '#6B5544',
            'text_muted' => '#9C8875',
        ]);

        $this->assertSame('#2E4D8A', $vars['--clay-red']);
        $this->assertSame('#2E4D8A', $vars['--theme-primary']);
        $this->assertSame('#4A72C4', $vars['--sunfire']);
        $this->assertSame('#D4A017', $vars['--savanna-gold']);
        $this->assertSame('#FAF6F0', $vars['--cream']);
    }

    public function test_dark_mode_lightens_primary_and_tints_background(): void
    {
        $service = app(WebPortalThemeService::class);

        $light = $service->toLightVariables(['primary' => '#2E4D8A', 'background' => '#FAF6F0']);
        $dark = $service->toDarkVariables(['primary' => '#2E4D8A', 'background' => '#FAF6F0']);

        $this->assertSame('#2E4D8A', $light['--clay-red']);
        $this->assertNotSame($light['--clay-red'], $dark['--clay-red']);
        $this->assertNotSame($light['--cream'], $dark['--cream']);
        $this->assertSame('#f3f4f6', $dark['--ink']);
    }

    public function test_org_user_with_default_theme_gets_custom_primary_in_portal_css(): void
    {
        $org = Organisation::create([
            'name' => 'Web Theme School',
            'code' => 'web-theme-school',
            'plan' => 'school',
            'status' => 'active',
        ]);

        Theme::create([
            'org_id' => $org->id,
            'name' => 'Blue Brand',
            'slug' => 'blue_brand',
            'is_default' => true,
            'is_active' => true,
            'colors' => ['primary' => '#224488'],
        ]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'org_admin', 'guard_name' => 'web']);
        $user->assignRole('org_admin');

        $this->actingAs($user);

        $resolved = app(WebPortalThemeService::class)->forRequest();

        $this->assertTrue($resolved['theme_engine_enabled']);
        $this->assertSame('organisation_theme', $resolved['theme']['source']);
        $this->assertSame('#224488', $resolved['css_variables']['--clay-red']);
    }
}
