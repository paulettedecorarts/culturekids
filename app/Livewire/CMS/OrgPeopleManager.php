<?php

namespace App\Livewire\CMS;

use App\Actions\Organisation\InviteOrganisationMember;
use App\Models\Organisation;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class OrgPeopleManager extends Component
{
    public bool $showInviteModal = false;

    public string $inviteName = '';

    public string $inviteEmail = '';

    public string $inviteRole = 'teacher';

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

    public function invite(InviteOrganisationMember $invite): void
    {
        $org = auth()->user()?->organisation;
        if (! $org instanceof Organisation) {
            abort(403);
        }

        $this->validate([
            'inviteName' => ['required', 'string', 'max:255'],
            'inviteEmail' => ['required', 'email', 'max:255', 'unique:users,email'],
            'inviteRole' => ['required', 'in:teacher,child'],
        ], [], [
            'inviteName' => 'name',
            'inviteEmail' => 'email',
            'inviteRole' => 'role',
        ]);

        try {
            $invite(
                $org,
                $this->inviteName,
                $this->inviteEmail,
                $this->inviteRole,
                auth()->user(),
            );
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
                __('Invitation sent. They will receive an email to choose a password and can then sign in.')
            );
        }

        $this->inviteName = '';
        $this->inviteEmail = '';
        $this->inviteRole = 'teacher';
        $this->resetErrorBag();
        $this->showInviteModal = false;
    }

    public function render()
    {
        $org = auth()->user()?->organisation;

        $members = collect();
        if ($org) {
            $members = User::query()
                ->where('organisation_id', $org->id)
                ->with('roles')
                ->orderBy('name')
                ->get();
        }

        return view('livewire.cms.org-people-manager', [
            'organization' => $org,
            'members' => $members,
        ]);
    }
}
