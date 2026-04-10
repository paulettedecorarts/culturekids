<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationComicDecision extends Model
{
    public const DECISION_APPROVED = 'approved';

    public const DECISION_REJECTED = 'rejected';

    protected $fillable = [
        'organisation_id',
        'comic_id',
        'decision',
        'decided_by',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
