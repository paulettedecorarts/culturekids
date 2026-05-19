<?php

namespace Tests\Feature;

use App\Livewire\CMS\Organizations;
use App\Models\Module;
use App\Models\Organisation;
use App\Models\User;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CmsOrganisationModuleToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
    }

    public function test_org_admin_can_disable_stories_module_for_their_organisation(): void
    {
        $org = Organisation::create([
            'name' => 'CMS Toggle School',
            'code' => 'cms-toggle-school',
            'plan' => 'school',
            'status' => 'active',
        ]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'org_admin', 'guard_name' => 'web']);
        $user->assignRole('org_admin');

        $storiesId = Module::query()->where('key', 'stories')->value('id');

        Livewire::actingAs($user)
            ->test(Organizations::class)
            ->call('toggleOrgModule', $storiesId)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('module_organisation', [
            'organisation_id' => $org->id,
            'module_id' => $storiesId,
            'is_enabled' => false,
        ]);
    }
}
