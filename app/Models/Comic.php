<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comic extends Model
{
    protected $fillable = [
        'org_id',
        'tribe_id',
        'title',
        'description',
        'age_min',
        'age_max',
        'status',
        'cover_image_path',
        'bundle_path',
        'bundle_hash',
        'star_points',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'age_min' => 'integer',
        'age_max' => 'integer',
        'star_points' => 'integer',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'org_id');
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function panels(): HasMany
    {
        return $this->hasMany(ComicPanel::class)->orderBy('order_index');
    }

    /**
     * Get age range string (e.g., "2-3", "3-4")
     */
    public function getAgeRangeAttribute(): string
    {
        return "{$this->age_min}-{$this->age_max}";
    }

    /**
     * Scope for published comics
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for draft comics
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for comics in review
     */
    public function scopeInReview($query)
    {
        return $query->where('status', 'review');
    }
}
