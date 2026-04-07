<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
     * Modules available to this organisation
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_organisation')
            ->withPivot('is_enabled')
            ->withTimestamps();
    }
}
