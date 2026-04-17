<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ChildProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'child_user_id',
        'name',
        'avatar',
        'dob',
        'age_band',
        'age_profile_id',
        'total_stars',
    ];

    /**
     * Get the parent user who created this profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the child's own user account
     */
    public function childUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_user_id');
    }

    protected static function booted(): void
    {
        static::saving(function (ChildProfile $profile): void {
            if ($profile->age_profile_id) {
                $assigned = AgeProfile::find($profile->age_profile_id);
                if ($assigned) {
                    $profile->age_band = $assigned->age_range_label;
                    return;
                }
            }

            if (! $profile->dob) {
                return;
            }

            $age = Carbon::parse($profile->dob)->age;
            $resolved = AgeProfile::query()
                ->where('is_active', true)
                ->where('min_age', '<=', $age)
                ->where(function ($query) use ($age): void {
                    $query->whereNull('max_age')->orWhere('max_age', '>=', $age);
                })
                ->orderByDesc('min_age')
                ->first();

            if (! $resolved) {
                return;
            }

            $profile->age_profile_id = $resolved->id;
            $profile->age_band = $resolved->age_range_label;
        });
    }

    public function ageProfile(): BelongsTo
    {
        return $this->belongsTo(AgeProfile::class, 'age_profile_id');
    }

    public function ageCategory(): BelongsTo
    {
        return $this->ageProfile();
    }

    /**
     * Get the progress events for this child
     */
    public function progressEvents(): HasMany
    {
        return $this->hasMany(ProgressEvent::class);
    }
}
