<?php

namespace App\Services;

use App\Models\ComicPanel;
use App\Models\ContentTranslation;
use App\Models\OrganisationContentDecision;

class ContentTranslationPersistenceService
{
    public function __construct(
        protected ContentTranslationSubItemResolver $subItems,
    ) {}

    public function applyNativeSync(ContentTranslation $translation): void
    {
        if ($translation->content_type === OrganisationContentDecision::TYPE_STORY) {
            return;
        }

        $this->subItems->applyNative(
            $translation->content_type,
            (int) $translation->content_id,
            $translation->sub_item_key,
            $translation->word,
            $translation->translation,
            $translation->phonetic,
        );
    }

    public function hydrateFromSubItem(ContentTranslation $translation): void
    {
        if ($translation->content_type === OrganisationContentDecision::TYPE_STORY && $translation->sub_item_key) {
            $panelId = (int) str_replace('panel:', '', $translation->sub_item_key);
            $translation->panel_id = $panelId ?: $translation->panel_id;
            if ($panelId && ! $translation->content_id) {
                $translation->content_id = ComicPanel::query()->whereKey($panelId)->value('comic_id') ?? 0;
            }
        }
    }
}
