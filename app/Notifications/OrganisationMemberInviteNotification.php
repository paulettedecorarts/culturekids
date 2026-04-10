<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class OrganisationMemberInviteNotification extends ResetPassword
{
    public function __construct(
        #[\SensitiveParameter]
        string $token,
        public string $organisationName,
        public string $roleLabel,
    ) {
        parent::__construct($token);
    }

    /**
     * @param  string  $url
     */
    protected function buildMailMessage($url): MailMessage
    {
        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
        $loginUrl = url(route('login', [], false));

        return (new MailMessage)
            ->subject(__('You are invited to join :org', ['org' => $this->organisationName]))
            ->line(__(':org has added you as a :role on Paulette Culture Kids.', [
                'org' => $this->organisationName,
                'role' => $this->roleLabel,
            ]))
            ->line(__('Choose a password using the button below. Then sign in at :url with this email address and the password you set.', ['url' => $loginUrl]))
            ->line(__('After you sign in, use your dashboard to get started with lessons and activities.'))
            ->action(__('Choose password'), $url)
            ->line(__('This link expires in :count minutes.', ['count' => $minutes]));
    }
}
