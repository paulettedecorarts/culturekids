<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Story panel vocabulary — scoped view of content_translations.
 */
class PanelVocabTag extends ContentTranslation
{
    protected $table = 'content_translations';

    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('storyPanel', function (Builder $query): void {
            $query->where('content_type', OrganisationContentDecision::TYPE_STORY)
                ->whereNotNull('panel_id');
        });

        static::creating(function (PanelVocabTag $tag): void {
            $tag->content_type = OrganisationContentDecision::TYPE_STORY;
            if ($tag->panel_id && ! $tag->content_id) {
                $tag->content_id = ComicPanel::query()->whereKey($tag->panel_id)->value('comic_id') ?? 0;
            }
            $tag->sub_item_key = $tag->panel_id ? 'panel:'.$tag->panel_id : $tag->sub_item_key;
        });

        static::updating(function (PanelVocabTag $tag): void {
            if ($tag->panel_id) {
                $tag->sub_item_key = 'panel:'.$tag->panel_id;
                if (! $tag->content_id) {
                    $tag->content_id = ComicPanel::query()->whereKey($tag->panel_id)->value('comic_id') ?? 0;
                }
            }
        });
    }
}
