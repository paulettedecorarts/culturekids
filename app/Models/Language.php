<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'name',
        'native_name',
        'code',
        'flag_emoji',
        'translation_coverage',
        'audio_pack_available',
        'status',
        'is_active',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'translation_coverage' => 'integer',
        'audio_pack_available' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
