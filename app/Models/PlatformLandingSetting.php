<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformLandingSetting extends Model
{
    protected $fillable = [
        'draft',
        'published',
        'published_at',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'draft' => 'array',
            'published' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            ['draft' => [], 'published' => []]
        );
    }
}
