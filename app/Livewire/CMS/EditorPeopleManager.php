<?php

namespace App\Livewire\CMS;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class EditorPeopleManager extends Component
{
    public bool $showInviteModal = false;

    public string $inviteName = '';

    public string $inviteEmail = '';

    public function openInviteModal(): void
    {
        $this->resetValidation();
        $this->showInviteModal = true;
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
        $this->resetValidation();
    }

    public function invite(): void
    {
        $this->validate([
            'inviteName' => ['required', 'string', 'max:255'],
            'inviteEmail' => ['required', 'email', 'max:255', 'unique:users,email'],
        ], [], [
            'inviteName' => 'name',
            'inviteEmail' => 'email',
        ]);

        try {
            // Create the CMS editor user
            $user = User::create([
                'name' => $this->inviteName,
                'email' => $this->inviteEmail,
                'password' => Hash::make(uniqid()), // Temporary password
                'email_verified_at' => now(),
            ]);

            // Assign cms_editor role
            $user->assignRole('cms_editor');

            // Send password reset email
            Password::sendResetLink(['email' => $this->inviteEmail]);

        } catch (\Throwable $e) {
            report($e);
            $this->addError('inviteEmail', __('Could not send the invitation. Check mail settings and try again.'));

            return;
        }

        if (config('mail.default') === 'log') {
            session()->flash(
                'message',
                __('Invitation created. Mail is using the log driver — check storage/logs/laravel.log for the message, or set MAIL_MAILER=smtp to send real email.')
            );
        } else {
            session()->flash(
                'message',
                __('Invitation sent. They will receive an email to set their password and can then sign in.')
            );
        }

        $this->inviteName = '';
        $this->inviteEmail = '';
        $this->resetErrorBag();
        $this->showInviteModal = false;
    }

    public function render()
    {
        $editors = User::query()
            ->role('cms_editor')
            ->with('roles')
            ->orderBy('name')
            ->get();

        return view('livewire.cms.editor-people-manager', [
            'editors' => $editors,
        ]);
    }
}
