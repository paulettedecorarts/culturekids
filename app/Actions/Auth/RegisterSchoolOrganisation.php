<?php

namespace App\Actions\Auth;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RegisterSchoolOrganisation
{
    /**
     * Create a school organisation and its org admin user.
     */
    public function register(
        string $organisationName,
        string $adminName,
        string $email,
        string $password,
    ): User {
        return DB::transaction(function () use ($organisationName, $adminName, $email, $password) {
            $org = Organisation::create([
                'name' => $organisationName,
                'code' => $this->uniqueOrganisationCode($organisationName),
                'plan' => 'free',
                'status' => 'active',
            ]);

            $user = User::create([
                'name' => $adminName,
                'email' => Str::lower($email),
                'password' => Hash::make($password),
                'organisation_id' => $org->id,
            ]);

            Role::firstOrCreate(['name' => 'org_admin', 'guard_name' => 'web']);
            $user->assignRole('org_admin');

            return $user;
        });
    }

    private function uniqueOrganisationCode(string $name): string
    {
        $base = Str::slug($name) ?: 'school';
        $code = $base;
        $suffix = 1;

        while (Organisation::where('code', $code)->exists()) {
            $code = $base.'-'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
