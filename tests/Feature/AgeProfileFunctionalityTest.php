<?php

namespace Tests\Feature;

use App\Livewire\Admin\AgeProfileDetailPage;
use App\Models\AgeProfile;
use App\Models\ChildProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AgeProfileFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    public function test_age_profiles_endpoint_requires_sanctum_auth(): void
    {
        $this->getJson('/api/age-profiles')->assertUnauthorized();
    }

    public function test_super_admin_can_fetch_age_profiles_via_api(): void
    {
        $user = $this->createSuperAdminUser();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/age-profiles');

        $response->assertOk()
            ->assertJsonCount(4, 'age_profiles')
            ->assertJsonPath('age_profiles.0.key', 'early_explorers');
    }

    public function test_super_admin_login_me_and_logout_flow_works(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@culturekids.app',
            'password' => Hash::make('password'),
        ]);
        $this->ensureRole('super_admin');
        $user->assignRole('super_admin');

        $login = $this->postJson('/api/auth/login', [
            'email' => 'admin@culturekids.app',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('message', 'Login successful');

        $token = $login->json('token');
        $this->assertNotEmpty($token);

        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/auth/me', $headers)
            ->assertOk()
            ->assertJsonPath('user.email', 'admin@culturekids.app');

        $this->postJson('/api/auth/logout', [], $headers)
            ->assertOk()
            ->assertJsonPath('message', 'Successfully logged out');
    }

    public function test_child_profile_auto_assigns_age_profile_from_dob(): void
    {
        $user = User::factory()->create();
        $profile = ChildProfile::create([
            'user_id' => $user->id,
            'name' => 'Amina',
            'dob' => now()->subYears(4)->toDateString(),
            'age_band' => 'pending',
            'total_stars' => 0,
        ]);

        $profile->refresh();

        $this->assertNotNull($profile->age_profile_id);
        $this->assertSame('4-5', $profile->age_band);
        $this->assertSame('young_thinkers', $profile->ageProfile?->key);
    }

    public function test_age_profile_detail_page_allows_editing_creating_and_deleting_when_unassigned(): void
    {
        $user = $this->createSuperAdminUser();
        $target = AgeProfile::where('key', 'young_thinkers')->firstOrFail();

        $this->actingAs($user);

        Livewire::test(AgeProfileDetailPage::class, ['id' => $target->id])
            ->call('startEditing')
            ->set('name', 'Young Thinkers Updated')
            ->set('key', 'young_thinkers')
            ->set('min_age', 4)
            ->set('max_age', 5)
            ->set('ui_scale', 'standard')
            ->set('touch_target_px', 52)
            ->set('reading_level', 'short_words')
            ->set('activity_complexity', 'multi_choice')
            ->set('is_audio_first', false)
            ->set('is_active', true)
            ->set('sort_order', 30)
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('age_profiles', [
            'id' => $target->id,
            'name' => 'Young Thinkers Updated',
        ]);

        Livewire::test(AgeProfileDetailPage::class)
            ->set('name', 'Teens')
            ->set('key', 'teens_13_15')
            ->set('min_age', 13)
            ->set('max_age', 15)
            ->set('ui_scale', 'compact')
            ->set('touch_target_px', 42)
            ->set('reading_level', 'sentences')
            ->set('activity_complexity', 'open_ended')
            ->set('is_audio_first', false)
            ->set('is_active', true)
            ->set('sort_order', 60)
            ->call('saveProfile')
            ->assertHasNoErrors();

        $created = AgeProfile::where('key', 'teens_13_15')->firstOrFail();
        $this->assertDatabaseHas('age_profiles', ['id' => $created->id, 'name' => 'Teens']);

        Livewire::test(AgeProfileDetailPage::class, ['id' => $created->id])
            ->call('deleteProfile')
            ->assertHasNoErrors();
        $this->assertDatabaseMissing('age_profiles', ['id' => $created->id]);
    }

    private function createSuperAdminUser(): User
    {
        $user = User::factory()->create();
        $this->ensureRole('super_admin');
        $user->assignRole('super_admin');

        return $user;
    }

    private function ensureRole(string $name): void
    {
        Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }
}
