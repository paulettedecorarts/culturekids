<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class OrganisationAdminWelcomeNotification extends ResetPassword
{
    public function __construct(
        #[\SensitiveParameter]
        string $token,
        public string $organisationName,
    ) {
        parent::__construct($token);
    }

    /**
     * @param  string  $url
     */
    protected function buildMailMessage($url): MailMessage
    {
        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject(__('You are invited to administer :org', ['org' => $this->organisationName]))
            ->line(__('You have been set up as the organisation administrator for :org.', ['org' => $this->organisationName]))
            ->line(__('Use the button below to choose your password. After that you can sign in with your email and that password.'))
            ->action(__('Choose password'), $url)
            ->line(__('This link expires in :count minutes.', ['count' => $minutes]));
    }
}
