<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpotDifferenceZone extends Model
{
    protected $fillable = [
        'spot_difference_id', 'x_percent', 'y_percent',
        'radius_percent', 'label', 'order_index',
    ];

    protected $casts = [
        'x_percent'      => 'float',
        'y_percent'      => 'float',
        'radius_percent' => 'float',
        'order_index'    => 'integer',
    ];

    public function spotDifference(): BelongsTo
    {
        return $this->belongsTo(SpotDifference::class);
    }
}
