<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_id',
        'name',
        'slug',
        'description',
        'is_default',
        'is_active',
        'colors',
        'typography',
        'spacing',
        'borders',
        'metadata',
        'preview_image',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'colors' => 'array',
        'typography' => 'array',
        'spacing' => 'array',
        'borders' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get default theme colors structure
     */
    public static function defaultColors(): array
    {
        return [
            'primary' => '#C44B2B',
            'secondary' => '#E8872A',
            'accent' => '#D4A017',
            'success' => '#4A7C59',
            'warning' => '#F2A84E',
            'danger' => '#9A3218',
            'background' => '#FAF6F0',
            'surface' => '#FFFFFF',
            'text_primary' => '#1A1208',
            'text_secondary' => '#6B5544',
            'text_muted' => '#9C8875',
        ];
    }

    /**
     * Get the organization that owns this theme
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'org_id');
    }
}
