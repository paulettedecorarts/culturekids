<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LessonPlan extends Model
{
    public const STATUS_PLANNED = 'planned';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'classroom_id',
        'lessonable_id',
        'lessonable_type',
        'scheduled_on',
        'status',
        'sort_order',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'scheduled_on' => 'date',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function lessonable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
