<?php

namespace App\Actions\Organisation;

use App\Models\AuditLog;
use App\Models\Organisation;
use App\Models\User;
use App\Notifications\OrganisationMemberInviteNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class InviteOrganisationMember
{
    /**
     * Invite a teacher or student: creates the user, assigns the role, emails a password-setup link.
     *
     * @param  'teacher'|'student'  $roleName
     */
    public function __invoke(
        Organisation $organisation,
        string $name,
        string $email,
        string $roleName,
        ?User $invitedBy = null,
    ): User {
        if (! in_array($roleName, ['teacher', 'student'], true)) {
            throw new \InvalidArgumentException('Invalid role for organisation invite.');
        }

        return DB::transaction(function () use ($organisation, $name, $email, $roleName, $invitedBy) {
            $user = User::create([
                'name' => $name,
                'email' => Str::lower($email),
                'password' => Hash::make(Str::random(64)),
                'organisation_id' => $organisation->id,
                'email_verified_at' => now(),
            ]);
            $user->assignRole($roleName);

            $roleLabel = $roleName === 'teacher' ? __('Teacher') : __('Student');

            $status = Password::broker()->sendResetLink(
                ['email' => $user->email],
                function (User $u, string $token) use ($organisation, $roleLabel): void {
                    $u->notify(new OrganisationMemberInviteNotification($token, $organisation->name, $roleLabel));
                }
            );

            if ($status !== Password::RESET_LINK_SENT) {
                throw new \RuntimeException($status);
            }

            AuditLog::record('ORG_INVITE_MEMBER', "organisations/{$organisation->id}", [
                'email' => $user->email,
                'role' => $roleName,
                'invited_by_user_id' => $invitedBy?->id,
            ]);

            return $user;
        });
    }
}
