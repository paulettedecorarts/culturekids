<?php

namespace App\Services;

use App\Models\ActivityFlashcardSlide;
use App\Models\ComicPanel;
use App\Models\ContentTranslation;
use App\Models\LanguageActivityWord;
use App\Models\OrganisationContentDecision;
use App\Models\WordSearch;

class ContentTranslationPersistenceService
{
    public function applyNativeSync(ContentTranslation $translation): void
    {
        match ($translation->content_type) {
            OrganisationContentDecision::TYPE_LANGUAGE => $this->syncLanguageWord($translation),
            OrganisationContentDecision::TYPE_FLASHCARD => $this->syncFlashcardSlide($translation),
            OrganisationContentDecision::TYPE_WORD_SEARCH => $this->syncWordSearchEntry($translation),
            OrganisationContentDecision::TYPE_CULTURE => $this->syncCultureProverb($translation),
            default => null,
        };
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

    protected function syncLanguageWord(ContentTranslation $translation): void
    {
        if (! $translation->sub_item_key || ! str_starts_with($translation->sub_item_key, 'word:')) {
            return;
        }

        $wordId = (int) substr($translation->sub_item_key, 5);
        $word = LanguageActivityWord::query()->find($wordId);
        if (! $word || (int) $word->language_activity_id !== (int) $translation->content_id) {
            return;
        }

        $word->update([
            'word' => $translation->word,
            'translation' => $translation->translation,
            'phonetic' => $translation->phonetic,
        ]);
    }

    protected function syncFlashcardSlide(ContentTranslation $translation): void
    {
        if (! $translation->sub_item_key || ! str_starts_with($translation->sub_item_key, 'slide:')) {
            return;
        }

        $slideId = (int) substr($translation->sub_item_key, 6);
        $slide = ActivityFlashcardSlide::query()->find($slideId);
        if (! $slide || (int) $slide->activity_id !== (int) $translation->content_id) {
            return;
        }

        $slide->update([
            'front_label' => $translation->word,
            'back_label' => $translation->translation,
            'phonetic' => $translation->phonetic,
        ]);
    }

    protected function syncWordSearchEntry(ContentTranslation $translation): void
    {
        if (! $translation->sub_item_key || ! str_starts_with($translation->sub_item_key, 'ws:')) {
            return;
        }

        $index = (int) substr($translation->sub_item_key, 3);
        $ws = WordSearch::query()->find($translation->content_id);
        if (! $ws || ! is_array($ws->words)) {
            return;
        }

        $words = $ws->words;
        if (! isset($words[$index]) || ! is_array($words[$index])) {
            return;
        }

        $words[$index]['word'] = $translation->word;
        $words[$index]['translation'] = $translation->translation;
        if ($translation->phonetic !== null) {
            $words[$index]['hint'] = $translation->phonetic;
        }

        $ws->update(['words' => $words]);
    }

    protected function syncCultureProverb(ContentTranslation $translation): void
    {
        if ($translation->sub_item_key !== null) {
            return;
        }

        \App\Models\CultureActivity::query()
            ->whereKey($translation->content_id)
            ->update([
                'proverb' => $translation->word,
                'proverb_translation' => $translation->translation,
            ]);
    }
}
