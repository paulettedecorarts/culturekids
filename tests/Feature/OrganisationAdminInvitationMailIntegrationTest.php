<?php

namespace Tests\Feature;

use App\Livewire\Admin\OrganizationCreate;
use App\Models\Organisation;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Sends a real invitation email via your configured mailer (e.g. Mailtrap).
 *
 * Default PHPUnit sets MAIL_MAILER=array; this test overrides config to SMTP using .env values.
 *
 * Run (PowerShell):
 *   $env:MAIL_INTEGRATION_TEST='1'; php artisan test --filter=OrganisationAdminInvitationMailIntegrationTest
 */
class OrganisationAdminInvitationMailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! filter_var(env('MAIL_INTEGRATION_TEST', false), FILTER_VALIDATE_BOOL)) {
            return;
        }

        // PHPUnit forces MAIL_MAILER=array; use real SMTP from .env for this suite.
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => env('MAIL_HOST', '127.0.0.1'),
            'mail.mailers.smtp.port' => env('MAIL_PORT', 2525),
            'mail.mailers.smtp.username' => env('MAIL_USERNAME'),
            'mail.mailers.smtp.password' => env('MAIL_PASSWORD'),
            'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION', 'tls'),
        ]);
    }

    public function test_livewire_create_organisation_sends_real_invite_email(): void
    {
        if (! filter_var(env('MAIL_INTEGRATION_TEST', false), FILTER_VALIDATE_BOOL)) {
            $this->markTestSkipped('Set MAIL_INTEGRATION_TEST=1 in the environment to run live SMTP (e.g. Mailtrap).');
        }

        $this->seed(RoleSeeder::class);

        $super = User::factory()->create();
        $super->assignRole('super_admin');
        $this->actingAs($super);

        $adminEmail = 'org-invite-integration-'.uniqid('', true).'@example.test';

        $test = Livewire::test(OrganizationCreate::class)
            ->set('name', 'Mail integration school')
            ->set('code', 'mail-int-'.uniqid())
            ->set('plan', 'school')
            ->set('status', 'active')
            ->set('admin_name', 'Integration Admin')
            ->set('admin_email', $adminEmail)
            ->call('save');

        if ($test->errors()->has('admin_email')) {
            fwrite(STDERR, print_r($test->errors()->get('admin_email'), true));
        }

        $test->assertHasNoErrors();

        $admin = User::where('email', $adminEmail)->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('org_admin'));

        $this->assertTrue(
            DB::table('password_reset_tokens')->where('email', $adminEmail)->exists(),
            'Password broker should store a reset token after sending the invite.'
        );

        $org = Organisation::find($admin->organisation_id);
        $this->assertNotNull($org);
    }
}
