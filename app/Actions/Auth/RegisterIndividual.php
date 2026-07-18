<?php

namespace App\Actions\Auth;

use App\Models\ChildProfile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RegisterIndividual
{
    /**
     * Create a solo learner account (no organisation, no family hub).
     */
    public function register(string $name, string $email, string $password): User
    {
        $user = User::create([
            'name' => $name,
            'email' => Str::lower($email),
            'password' => Hash::make($password),
            'organisation_id' => null,
        ]);

        Role::firstOrCreate(['name' => 'individual', 'guard_name' => 'web']);
        $user->assignRole('individual');

        ChildProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'dob' => Carbon::now()->subYears(18)->toDateString(),
            'age_band' => 'full',
            'total_stars' => 0,
        ]);

        return $user;
    }
}
