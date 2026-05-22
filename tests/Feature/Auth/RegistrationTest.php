<?php

namespace Tests\Feature\Auth;

use App\Mail\VerificationCodeMail;
use App\Models\Organisation;
use App\Models\User;
use App\Models\VerificationCode;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register')
            ->assertSee('Register your school')
            ->assertSee('Organisation Name');
    }

    public function test_new_school_organisation_can_register_without_auto_login(): void
    {
        Mail::fake();

        $component = Volt::test('pages.auth.register')
            ->set('organisation_name', 'Sunrise Primary')
            ->set('admin_name', 'Jane Admin')
            ->set('email', 'admin@sunrise.test')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('verification.enter-code', absolute: false));

        $this->assertGuest();

        $user = User::where('email', 'admin@sunrise.test')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('org_admin'));

        $org = Organisation::find($user->organisation_id);
        $this->assertNotNull($org);
        $this->assertSame('Sunrise Primary', $org->name);

        Mail::assertSent(VerificationCodeMail::class, fn ($mail) => $mail->hasTo('admin@sunrise.test'));
    }

    public function test_unverified_user_sees_dialog_on_login(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'pending@school.test',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('org_admin');

        Volt::test('pages.auth.login')
            ->set('form.email', 'pending@school.test')
            ->set('form.password', 'password')
            ->call('login')
            ->assertSet('showUnverifiedDialog', true)
            ->assertSet('unverifiedEmail', 'pending@school.test')
            ->assertHasNoErrors();

        $this->assertGuest();
        $this->assertSame($user->id, session('pending_verification_user_id'));
    }

    public function test_verified_user_can_login_with_code(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create([
            'email' => 'ready@school.test',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('org_admin');

        $code = VerificationCode::createForUser($user);

        session([
            'pending_verification_user_id' => $user->id,
            'pending_verification_remember' => false,
        ]);

        Volt::test('pages.auth.verify-email-code')
            ->set('code', $code->code)
            ->call('verifyCode')
            ->assertHasNoErrors();

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);
    }

    public function test_super_admin_can_login_without_email_verification(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'admin@culturekids.test',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('super_admin');

        Volt::test('pages.auth.login')
            ->set('form.email', 'admin@culturekids.test')
            ->set('form.password', 'password')
            ->call('login')
            ->assertSet('showUnverifiedDialog', false)
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }
}
