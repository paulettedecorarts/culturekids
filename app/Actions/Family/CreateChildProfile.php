<?php

namespace App\Actions\Family;

use App\Models\ChildProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class CreateChildProfile
{
    /**
     * @return array{profile: ChildProfile, child_email: string}
     */
    public function create(
        User $parent,
        string $name,
        string $dateOfBirth,
        string $pin,
        ?string $avatar = null,
    ): array {
        $childEmail = $this->generateChildEmail($parent->email, $name);

        $childUser = User::create([
            'name' => $name,
            'email' => $childEmail,
            'password' => Hash::make($pin),
            'organisation_id' => $parent->organisation_id,
            'email_verified_at' => now(),
        ]);

        Role::firstOrCreate(['name' => 'child', 'guard_name' => 'web']);
        $childUser->assignRole('child');

        $profile = ChildProfile::create([
            'user_id' => $parent->id,
            'child_user_id' => $childUser->id,
            'name' => $name,
            'avatar' => $avatar,
            'dob' => $dateOfBirth,
            'total_stars' => 0,
        ]);

        $profile->refresh();

        return [
            'profile' => $profile,
            'child_email' => $childEmail,
        ];
    }

    private function generateChildEmail(string $parentEmail, string $childName): string
    {
        $parts = explode('@', $parentEmail);
        $username = $parts[0];
        $domain = $parts[1] ?? 'family.local';

        $childSlug = Str::slug($childName) ?: 'child';
        $email = "{$username}.{$childSlug}@{$domain}";
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = "{$username}.{$childSlug}{$counter}@{$domain}";
            $counter++;
        }

        return $email;
    }
}
