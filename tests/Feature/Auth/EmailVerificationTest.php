<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\VerificationCode;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Volt\Volt;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_verify_email_code_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('org_admin');

        $response = $this->withSession([
            'pending_verification_user_id' => $user->id,
        ])->get('/verify-email-code');

        $response
            ->assertSeeVolt('pages.auth.verify-email-code')
            ->assertStatus(200)
            ->assertSee('Enter verification code');
    }

    public function test_email_can_be_verified_with_code_without_prior_login(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('org_admin');

        Event::fake();

        $code = VerificationCode::createForUser($user);

        Volt::test('pages.auth.verify-email-code')
            ->withSession(['pending_verification_user_id' => $user->id])
            ->set('code', $code->code)
            ->call('verifyCode')
            ->assertHasNoErrors();

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_code_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('org_admin');

        VerificationCode::createForUser($user);

        Volt::test('pages.auth.verify-email-code')
            ->withSession(['pending_verification_user_id' => $user->id])
            ->set('code', '000000')
            ->call('verifyCode')
            ->assertHasErrors('code');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->assertGuest();
    }

    public function test_authenticated_unverified_user_is_redirected_to_code_entry(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('org_admin');

        $this->actingAs($user)
            ->get(route('cms.admin.dashboard'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertRedirect(route('verification.enter-code'));
    }

    public function test_verified_org_admin_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('org_admin');

        $this->actingAs($user)
            ->get(route('cms.admin.dashboard'))
            ->assertOk();
    }

    public function test_unverified_super_admin_can_access_admin_dashboard(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }
}
