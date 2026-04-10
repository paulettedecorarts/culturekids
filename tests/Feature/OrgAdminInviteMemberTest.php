<?php

namespace Tests\Feature;

use App\Livewire\CMS\OrgPeopleManager;
use App\Models\Organisation;
use App\Models\User;
use App\Notifications\OrganisationMemberInviteNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class OrgAdminInviteMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_org_admin_can_invite_teacher_and_notification_is_sent(): void
    {
        Notification::fake();
        $this->seed(RoleSeeder::class);

        $org = Organisation::create([
            'name' => 'Test School',
            'code' => 'test-school-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);
        $admin = User::factory()->create(['organisation_id' => $org->id]);
        $admin->assignRole('org_admin');

        $this->actingAs($admin);

        Livewire::test(OrgPeopleManager::class)
            ->set('inviteName', 'Taylor Teacher')
            ->set('inviteEmail', 'taylor-teacher-invite@example.test')
            ->set('inviteRole', 'teacher')
            ->call('invite')
            ->assertHasNoErrors();

        $invited = User::where('email', 'taylor-teacher-invite@example.test')->first();
        $this->assertNotNull($invited);
        $this->assertSame($org->id, $invited->organisation_id);
        $this->assertTrue($invited->hasRole('teacher'));

        Notification::assertSentTo($invited, OrganisationMemberInviteNotification::class);
    }

    public function test_org_admin_can_invite_child_and_notification_is_sent(): void
    {
        Notification::fake();
        $this->seed(RoleSeeder::class);

        $org = Organisation::create([
            'name' => 'Test School',
            'code' => 'test-school-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);
        $admin = User::factory()->create(['organisation_id' => $org->id]);
        $admin->assignRole('org_admin');

        $this->actingAs($admin);

        Livewire::test(OrgPeopleManager::class)
            ->set('inviteName', 'Alex Child')
            ->set('inviteEmail', 'alex-child-invite@example.test')
            ->set('inviteRole', 'child')
            ->call('invite')
            ->assertHasNoErrors();

        $invited = User::where('email', 'alex-child-invite@example.test')->first();
        $this->assertNotNull($invited);
        $this->assertTrue($invited->hasRole('child'));

        Notification::assertSentTo($invited, OrganisationMemberInviteNotification::class);
    }
}
