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

#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'organisation_id',
        'email_verified_at',
        'current_tribe_id',
    ];

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

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
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
     * Only school org admins who self-register must verify email before sign-in.
     */
    public function requiresEmailVerification(): bool
    {
        return $this->hasRole('org_admin');
    }

    /**
     * In-app roles (super admin, CMS editor) and invited users are verified by default.
     */
    public function hasVerifiedEmail(): bool
    {
        if (! $this->requiresEmailVerification()) {
            return true;
        }

        return $this->email_verified_at !== null;
    }

    /**
     * Send a 6-digit verification code email (web + mobile), not a signed link.
     */
    public function sendEmailVerificationNotification(): void
    {
        if (! $this->requiresEmailVerification()) {
            return;
        }

        app(SendEmailVerificationCode::class)->send($this);
    }
}
