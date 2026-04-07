<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
        'is_enabled',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Organizations that have access to this module
     */
    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'module_organisation')
            ->withPivot('is_enabled')
            ->withTimestamps();
    }
}
