<?php

namespace App\Models;

use App\Actions\Auth\SendEmailVerificationCode;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'organisation_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the organisation this user belongs to
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Get the child profiles for this user
     */
    public function childProfiles(): HasMany
    {
        return $this->hasMany(ChildProfile::class);
    }

    /**
     * Device tokens registered for push notifications.
     */
    public function pushDeviceTokens(): HasMany
    {
        return $this->hasMany(PushDeviceToken::class);
    }

    /**
     * Classes where this user is the assigned teacher.
     */
    public function teachingClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    /**
     * Classes where this user is enrolled as a child (learner).
     */
    public function childClassrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class)->withTimestamps();
    }

    /**
     * Send a 6-digit verification code email (web + mobile), not a signed link.
     */
    public function sendEmailVerificationNotification(): void
    {
        app(SendEmailVerificationCode::class)->send($this);
    }
}
