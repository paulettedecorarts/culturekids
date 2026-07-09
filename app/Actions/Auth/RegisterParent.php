<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RegisterParent
{
    /**
     * Create a family (parent) account without an organisation.
     */
    public function register(string $name, string $email, string $password): User
    {
        $user = User::create([
            'name' => $name,
            'email' => Str::lower($email),
            'password' => Hash::make($password),
            'organisation_id' => null,
        ]);

        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');

        return $user;
    }
}
