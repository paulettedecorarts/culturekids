<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'impersonator_id',
        'action',
        'resource',
        'ip_address',
        'user_agent',
        'payload',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the impersonator (if action was performed while impersonating)
     */
    public function impersonator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'impersonator_id');
    }

    /**
     * Static helper to record an audit log
     */
    public static function record(string $action, ?string $resource = null, ?array $payload = null, string $status = 'success'): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'impersonator_id' => session('impersonator_id'),
            'action' => $action,
            'resource' => $resource,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'payload' => $payload,
            'status' => $status,
        ]);
    }
}
