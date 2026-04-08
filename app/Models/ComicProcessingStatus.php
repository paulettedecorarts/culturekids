<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComicProcessingStatus extends Model
{
    protected $table = 'comic_processing_status';

    protected $fillable = [
        'comic_id',
        'batch_id',
        'total_files',
        'processed_files',
        'failed_files',
        'status',
        'current_file',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_files' => 'integer',
        'processed_files' => 'integer',
        'failed_files' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_PROCESSING = 'processing';

    const STATUS_COMPLETED = 'completed';

    const STATUS_FAILED = 'failed';

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_files === 0) {
            return 0;
        }

        return (int) round(($this->processed_files / $this->total_files) * 100);
    }

    public function isProcessing(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function markAsProcessing(?string $currentFile = null): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'current_file' => $currentFile,
        ]);
    }

    public function incrementProcessed(): void
    {
        $this->increment('processed_files');
        $this->refresh();

        if ($this->processed_files >= $this->total_files) {
            $this->update([
                'status' => self::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        }
    }

    public function incrementFailed(?string $error = null): void
    {
        $this->increment('failed_files');

        if ($error) {
            $this->update(['error_message' => $error]);
        }
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
            'completed_at' => now(),
        ]);
    }
}
