<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    public const TYPE_LOGIN_ALERT = 'login_alert';

    public const TYPE_DOWNLOAD_REMINDER = 'download_reminder';

    public const TYPE_RECOMMENDATION = 'recommendation';

    public const TYPE_CONTENT_PUBLISHED = 'content_published';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'read_at',
        'push_sent',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'push_sent' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }
}
