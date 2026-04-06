<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'tribe_id',
        'type',
        'title',
        'description',
        'age_range',
        'star_points',
        'metadata',
        'is_published',
    ];

    protected $casts = [
        'metadata' => 'json',
        'is_published' => 'boolean',
    ];

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }
}
