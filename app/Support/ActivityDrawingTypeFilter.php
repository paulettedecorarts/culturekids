<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * drawing_kit activities mirror both drawings and colouring pages — filter by metadata.drawing_type.
 */
final class ActivityDrawingTypeFilter
{
    /** @var list<string> */
    public const COLOURING_TYPES = ['coloring', 'colour_by_number'];

    public static function isColouringType(?string $drawingType): bool
    {
        return in_array($drawingType, self::COLOURING_TYPES, true);
    }

    /**
     * @param  Builder<\App\Models\Activity>  $query
     */
    public static function applyColouringScope(Builder $query): void
    {
        $query->where('type', 'drawing_kit')
            ->where(function (Builder $inner): void {
                foreach (self::COLOURING_TYPES as $drawingType) {
                    $inner->orWhere('_drawing_type', $drawingType);
                }
            });
    }

    /**
     * @param  Builder<\App\Models\Activity>  $query
     */
    public static function applyDrawingScope(Builder $query): void
    {
        $query->where('type', 'drawing_kit')
            ->where(function (Builder $inner): void {
                $inner->whereNull('_drawing_type')
                    ->orWhere('_drawing_type', '')
                    ->orWhere('_drawing_type', 'null')
                    ->orWhereNotIn('_drawing_type', self::COLOURING_TYPES);
            });
    }

    public static function listTypeForActivity(\App\Models\Activity $activity): string
    {
        if ($activity->type !== 'drawing_kit') {
            return (string) $activity->type;
        }

        $drawingType = ActivityBundleMetadataExtract::toMetadataArray($activity)['drawing_type'] ?? null;

        return self::isColouringType(is_string($drawingType) ? $drawingType : null)
            ? 'colouring'
            : 'drawing_kit';
    }

    public static function moduleActivityTypeForDrawing(?string $drawingType): string
    {
        return self::isColouringType($drawingType) ? 'colouring' : 'drawing_kit';
    }

    /**
     * @param  Builder<\App\Models\Activity>  $query
     */
    public static function applyListTypeFilter(Builder $query, string $type): void
    {
        if ($type === 'colouring') {
            self::applyColouringScope($query);

            return;
        }

        if ($type === 'drawing_kit') {
            self::applyDrawingScope($query);

            return;
        }

        $query->where('type', $type);
    }
}
