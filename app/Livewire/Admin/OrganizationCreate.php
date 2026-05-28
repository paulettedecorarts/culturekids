<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Organisation;
use App\Models\User;
use App\Notifications\OrganisationAdminWelcomeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Livewire\Concerns\LogsFileUploads;
use App\Livewire\Concerns\ValidatesOnlyChangedOnEdit;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class OrganizationCreate extends Component
{
    use LogsFileUploads;
    use ValidatesOnlyChangedOnEdit;
    use WithFileUploads;

    public string $name = '';

    public string $code = '';

    public ?string $description = null;

    public ?string $address = null;

    public string $plan = 'free';

    public string $status = 'active';

    public $logo;

    public string $admin_name = '';

    public string $admin_email = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|min:3|max:100',
            'code' => 'required|alpha_dash|unique:organisations,code',
            'description' => 'nullable|string|max:500',
            'address' => 'nullable|max:255',
            'plan' => 'required|in:free,school,enterprise',
            'status' => 'required|in:active,inactive',
            'logo' => 'nullable|image|max:1024',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
        ];
    }

    public function updatedName($value): void
    {
        $this->code = Str::slug($value);
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'address' => $this->address,
            'plan' => $this->plan,
            'status' => $this->status,
        ];

        if ($this->logo) {
            $data['logo_url'] = $this->logo->store('logos', 'public');
        }

        try {
            DB::transaction(function () use ($data) {
                $org = Organisation::create($data);

                $admin = User::create([
                    'name' => $this->admin_name,
                    'email' => Str::lower($this->admin_email),
                    'password' => Hash::make(Str::random(64)),
                    'organisation_id' => $org->id,
                    'email_verified_at' => now(),
                ]);
                $admin->assignRole('org_admin');

                $status = Password::broker()->sendResetLink(
                    ['email' => $admin->email],
                    function (User $user, string $token) use ($org): void {
                        $user->notify(new OrganisationAdminWelcomeNotification($token, $org->name));
                    }
                );

                if ($status !== Password::RESET_LINK_SENT) {
                    throw new \RuntimeException($status);
                }

                AuditLog::record('CREATE_ORGANISATION', "organisations/{$org->id}", [
                    'code' => $org->code,
                    'plan' => $org->plan,
                    'admin_email' => $admin->email,
                ]);
            });
        } catch (\Throwable $e) {
            report($e);
            $this->addError('admin_email', $e->getMessage());

            return;
        }

        if (config('mail.default') === 'log') {
            session()->flash(
                'message',
                'Organization created. Mail is using the log driver, so the invitation was written to storage/logs/laravel.log (not a real inbox). Set MAIL_MAILER=smtp in .env and configure SMTP to send real email.'
            );
        } else {
            session()->flash(
                'message',
                'Organization created. An invitation email was sent to the organisation admin so they can choose a password and sign in.'
            );
        }

        $this->redirect(route('admin.organizations'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.organization-create');
    }
}
