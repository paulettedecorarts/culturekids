<?php

namespace App\Actions\Fortify;

use App\Actions\Auth\RegisterSchoolOrganisation;
use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered school organisation and org admin.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'organisation_name' => ['required', 'string', 'min:3', 'max:100'],
            'admin_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => $this->passwordRules(),
        ])->validate();

        return app(RegisterSchoolOrganisation::class)->register(
            $input['organisation_name'],
            $input['admin_name'],
            $input['email'],
            $input['password'],
        );
    }
}
