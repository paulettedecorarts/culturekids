<?php

namespace Tests\Feature;

use App\Livewire\Admin\ThemesManager;
use App\Models\Organisation;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ThemesManagerOrgAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_org_admin_sees_platform_and_organization_themes(): void
    {
        $org = Organisation::create([
            'name' => 'Theme School',
            'code' => 'theme-school',
            'plan' => 'school',
            'status' => 'active',
        ]);

        Theme::create([
            'org_id' => null,
            'name' => 'Culture Kids Default',
            'slug' => 'ck_default',
            'is_default' => true,
            'is_active' => true,
            'colors' => Theme::defaultColors(),
        ]);

        Theme::create([
            'org_id' => $org->id,
            'name' => 'School Custom',
            'slug' => 'school_custom',
            'is_default' => false,
            'is_active' => true,
            'colors' => array_merge(Theme::defaultColors(), ['primary' => '#111111']),
        ]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'org_admin', 'guard_name' => 'web']);
        $user->assignRole('org_admin');

        Livewire::actingAs($user)
            ->test(ThemesManager::class)
            ->assertViewHas('platformThemes', fn ($themes) => $themes->count() === 1 && $themes->first()->slug === 'ck_default')
            ->assertViewHas('orgThemes', fn ($paginator) => $paginator->total() === 1 && $paginator->first()->slug === 'school_custom');
    }

    public function test_org_admin_cannot_edit_platform_theme(): void
    {
        $org = Organisation::create([
            'name' => 'Theme School B',
            'code' => 'theme-school-b',
            'plan' => 'school',
            'status' => 'active',
        ]);

        $platform = Theme::create([
            'org_id' => null,
            'name' => 'Global Palette',
            'slug' => 'global_palette',
            'is_default' => true,
            'is_active' => true,
            'colors' => Theme::defaultColors(),
        ]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'org_admin', 'guard_name' => 'web']);
        $user->assignRole('org_admin');

        Livewire::actingAs($user)
            ->test(ThemesManager::class)
            ->call('edit', $platform->id)
            ->assertSet('showModal', false);
    }

    public function test_org_admin_adopts_platform_theme_as_organization_default(): void
    {
        $org = Organisation::create([
            'name' => 'Theme School C',
            'code' => 'theme-school-c',
            'plan' => 'school',
            'status' => 'active',
        ]);

        $platform = Theme::create([
            'org_id' => null,
            'name' => 'Savanna Global',
            'slug' => 'savanna_global',
            'is_default' => true,
            'is_active' => true,
            'colors' => array_merge(Theme::defaultColors(), ['primary' => '#ABCDEF']),
        ]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'org_admin', 'guard_name' => 'web']);
        $user->assignRole('org_admin');

        Livewire::actingAs($user)
            ->test(ThemesManager::class)
            ->call('setDefault', $platform->id);

        $this->assertDatabaseHas('themes', [
            'org_id' => $org->id,
            'is_default' => true,
        ]);

        $adopted = Theme::query()
            ->where('org_id', $org->id)
            ->where('is_default', true)
            ->first();

        $this->assertNotNull($adopted);
        $this->assertSame($platform->id, $adopted->metadata['platform_theme_id'] ?? null);
        $this->assertTrue($adopted->metadata['adopted_from_platform'] ?? false);
        $this->assertSame('#ABCDEF', $adopted->colors['primary']);

        $platform->refresh();
        $this->assertTrue($platform->is_default);
        $this->assertNull($platform->org_id);
    }

    public function test_org_admin_cannot_delete_platform_theme(): void
    {
        $org = Organisation::create([
            'name' => 'Theme School D',
            'code' => 'theme-school-d',
            'plan' => 'school',
            'status' => 'active',
        ]);

        $platform = Theme::create([
            'org_id' => null,
            'name' => 'Protected Global',
            'slug' => 'protected_global',
            'is_default' => false,
            'is_active' => true,
            'colors' => Theme::defaultColors(),
        ]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'org_admin', 'guard_name' => 'web']);
        $user->assignRole('org_admin');

        Livewire::actingAs($user)
            ->test(ThemesManager::class)
            ->call('delete', $platform->id);

        $this->assertDatabaseHas('themes', ['id' => $platform->id]);
    }
}
