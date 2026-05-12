<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clan extends Model
{
    protected $fillable = [
        'tribe_id', 'name', 'totem', 'totem_emoji', 'role',
        'region', 'description', 'history', 'proverb',
        'proverb_translation', 'color', 'cover_image_path',
        'is_active', 'sort_order', 'metadata',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
        'metadata'   => 'array',
    ];

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function cultureActivities(): HasMany
    {
        return $this->hasMany(CultureActivity::class, 'clan_name', 'name');
    }
}
