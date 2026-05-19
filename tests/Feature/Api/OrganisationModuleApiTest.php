<?php

namespace Tests\Feature\Api;

use App\Models\Module;
use App\Models\Organisation;
use App\Models\User;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganisationModuleApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
    }

    public function test_organisation_modules_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/organisation/modules')->assertUnauthorized();
    }

    public function test_b2c_user_receives_globally_enabled_modules(): void
    {
        $user = $this->createParentUser();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/organisation/modules')
            ->assertOk()
            ->assertJsonPath('organisation_id', null);

        $keys = collect($response->json('enabled_keys'));
        $this->assertTrue($keys->contains('stories'));
        $this->assertTrue($keys->contains('songs'));
        $this->assertCount(15, $response->json('modules'));
    }

    public function test_org_user_respects_per_org_module_override(): void
    {
        $org = Organisation::create([
            'name' => 'Test School',
            'code' => 'test-school',
            'plan' => 'school',
            'status' => 'active',
        ]);

        $stories = Module::query()->where('key', 'stories')->firstOrFail();
        $org->modules()->sync([
            $stories->id => ['is_enabled' => false],
        ]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $user->assignRole('teacher');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/organisation/modules')
            ->assertOk()
            ->assertJsonPath('organisation_id', $org->id);

        $this->assertNotContains('stories', $response->json('enabled_keys'));

        $storiesRow = collect($response->json('modules'))
            ->firstWhere('key', 'stories');
        $this->assertFalse($storiesRow['enabled']);
    }

    public function test_comics_api_returns_403_when_stories_module_disabled_for_org(): void
    {
        $org = Organisation::create([
            'name' => 'No Stories School',
            'code' => 'no-stories',
            'plan' => 'school',
            'status' => 'active',
        ]);

        $stories = Module::query()->where('key', 'stories')->firstOrFail();
        $org->modules()->sync([
            $stories->id => ['is_enabled' => false],
        ]);

        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/comics')->assertForbidden();
    }

    private function createParentUser(): User
    {
        $user = User::factory()->create(['organisation_id' => null]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');

        return $user;
    }
}
