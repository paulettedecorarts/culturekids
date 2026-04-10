<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Organisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status',
        'plan',
        'logo_url',
        'address',
        'description',
        'settings',
        'theme',
    ];

    protected $casts = [
        'settings' => 'array',
        'theme' => 'array',
    ];

    /**
     * Get the users belonging to this organisation
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Child profiles belonging to users in this organisation (typically parents).
     */
    public function childProfiles(): HasManyThrough
    {
        return $this->hasManyThrough(ChildProfile::class, User::class);
    }

    /**
     * When non-empty, API and catalog should limit tribes to these IDs.
     * Empty or missing list means no restriction (full heritage library).
     *
     * @return list<int>|null null = unrestricted; non-empty = allowed tribe ids only
     */
    public function restrictedTribeIds(): ?array
    {
        $ids = data_get($this->settings, 'allowed_tribe_ids');
        if (! is_array($ids) || $ids === []) {
            return null;
        }

        $out = array_values(array_unique(array_map('intval', $ids)));
        $out = array_values(array_filter($out));

        return $out === [] ? null : $out;
    }

    /**
     * Modules available to this organisation
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_organisation')
            ->withPivot('is_enabled')
            ->withTimestamps();
    }
}
