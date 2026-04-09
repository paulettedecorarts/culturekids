<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgeProfile extends Model
{
    protected $table = 'age_profiles';

    protected $fillable = [
        'name',
        'key',
        'min_age',
        'max_age',
        'icon_emoji',
        'color',
        'ui_scale',
        'touch_target_px',
        'reading_level',
        'activity_complexity',
        'content_access_rules',
        'ui_features',
        'is_audio_first',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_age' => 'integer',
        'max_age' => 'integer',
        'touch_target_px' => 'integer',
        'content_access_rules' => 'array',
        'ui_features' => 'array',
        'is_audio_first' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function childProfiles(): HasMany
    {
        return $this->hasMany(ChildProfile::class, 'age_profile_id');
    }

    public function getAgeRangeLabelAttribute(): string
    {
        if ($this->max_age === null) {
            return "{$this->min_age}+";
        }

        return "{$this->min_age}-{$this->max_age}";
    }
}
