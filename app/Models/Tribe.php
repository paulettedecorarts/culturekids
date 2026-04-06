<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tribe extends Model
{
    protected $fillable = [
        'name',
        'hero_name',
        'hero_emoji',
        'hero_icon',
        'greeting',
        'region',
        'color',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
