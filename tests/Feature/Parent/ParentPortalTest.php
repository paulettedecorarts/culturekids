<?php

namespace Tests\Feature\Parent;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ParentPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_parent_is_redirected_to_family_hub_after_login(): void
    {
        $parent = User::factory()->create([
            'email' => 'parent@family.test',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $parent->assignRole('parent');

        $this->actingAs($parent)
            ->get(route('dashboard'))
            ->assertRedirect(route('parent.dashboard', absolute: false));
    }

    public function test_parent_can_view_dashboard(): void
    {
        $parent = User::factory()->create(['email_verified_at' => now()]);
        $parent->assignRole('parent');

        $this->actingAs($parent)
            ->get(route('parent.dashboard'))
            ->assertOk()
            ->assertSee('Family Hub')
            ->assertSee('Hello');
    }

    public function test_parent_can_create_child_profile(): void
    {
        $parent = User::factory()->create(['email_verified_at' => now()]);
        $parent->assignRole('parent');

        $this->actingAs($parent);

        $component = \Livewire\Livewire::test(\App\Livewire\Parent\ChildForm::class)
            ->set('name', 'Amina')
            ->set('date_of_birth', '2018-05-10')
            ->set('pin', '1234')
            ->set('pin_confirmation', '1234')
            ->call('save');

        $child = \App\Models\ChildProfile::query()->where('user_id', $parent->id)->first();

        $component->assertRedirect(route('parent.tribe-access', absolute: false));

        $this->assertDatabaseHas('child_profiles', [
            'user_id' => $parent->id,
            'name' => 'Amina',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Amina',
        ]);
    }

    public function test_parent_without_children_is_sent_to_create_form_from_heritage(): void
    {
        $parent = User::factory()->create(['email_verified_at' => now()]);
        $parent->assignRole('parent');

        $this->actingAs($parent)
            ->get(route('heritage.app'))
            ->assertRedirect(route('parent.children.create', absolute: false));
    }

    public function test_teacher_cannot_access_parent_portal(): void
    {
        $teacher = User::factory()->create(['email_verified_at' => now()]);
        $teacher->assignRole('teacher');

        $this->actingAs($teacher)
            ->get(route('parent.dashboard'))
            ->assertForbidden();
    }

    public function test_parent_can_open_tribe_access_page(): void
    {
        $parent = User::factory()->create(['email_verified_at' => now()]);
        $parent->assignRole('parent');

        $this->actingAs($parent)
            ->get(route('parent.tribe-access'))
            ->assertOk()
            ->assertSee('Tribe access');
    }
}
