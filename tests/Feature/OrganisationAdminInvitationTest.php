<?php

namespace Tests\Feature;

use App\Livewire\Admin\OrganizationCreate;
use App\Models\Organisation;
use App\Models\User;
use App\Notifications\OrganisationAdminWelcomeNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class OrganisationAdminInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_organisation_creates_org_admin_and_sends_welcome_notification(): void
    {
        Notification::fake();
        $this->seed(RoleSeeder::class);

        $super = User::factory()->create();
        $super->assignRole('super_admin');

        $this->actingAs($super);

        Livewire::test(OrganizationCreate::class)
            ->set('name', 'Invited School')
            ->set('code', 'invited-school')
            ->set('plan', 'school')
            ->set('status', 'active')
            ->set('admin_name', 'Pat Admin')
            ->set('admin_email', 'pat-admin@example.test')
            ->call('save')
            ->assertHasNoErrors();

        $org = Organisation::where('code', 'invited-school')->first();
        $this->assertNotNull($org);

        $admin = User::where('email', 'pat-admin@example.test')->first();
        $this->assertNotNull($admin);
        $this->assertSame($org->id, $admin->organisation_id);
        $this->assertTrue($admin->hasRole('org_admin'));

        Notification::assertSentTo($admin, OrganisationAdminWelcomeNotification::class);
    }
}
