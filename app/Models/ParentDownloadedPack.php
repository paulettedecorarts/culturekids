<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentDownloadedPack extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tribe_id',
        'downloaded_at',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    /**
     * Get the user (parent) who downloaded this pack
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tribe for this pack
     */
    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }
}
