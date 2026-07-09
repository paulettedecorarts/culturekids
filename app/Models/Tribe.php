<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Tribe extends Model
{
    protected $fillable = [
        'name',
        'hero_name',
        'hero_emoji',
        'hero_icon',
        'greeting',
        'region',
        'color',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function heritageActivities(): HasMany
    {
        return $this->hasMany(Activity::class)->where('type', '!=', 'song');
    }

    public function clans(): HasMany
    {
        return $this->hasMany(Clan::class)->orderBy('sort_order');
    }

    public function parentsWithAccess(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_tribe_approvals')
            ->withPivot('approved_at');
    }

    /**
     * Emoji, image URL, or storage-backed tribe icon for API responses.
     */
    public function resolvedIcon(): ?string
    {
        if (filled($this->hero_icon) && str_contains((string) $this->hero_icon, '/')) {
            return Storage::disk('public')->url($this->hero_icon);
        }

        return $this->hero_emoji ?? $this->hero_icon;
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    public function comics(): HasMany
    {
        return $this->hasMany(Comic::class);
    }
}
