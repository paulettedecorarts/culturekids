<?php

namespace App\Observers;

use App\Models\LanguageActivity;
use App\Models\LanguageActivityWord;
use App\Models\ContentTranslation;
use App\Models\PanelVocabTag;
use App\Services\TranslationCoverageService;

class TranslationCoverageObserver
{
    public function __construct(
        private readonly TranslationCoverageService $coverage
    ) {}

    public function saved(LanguageActivity|LanguageActivityWord|ContentTranslation|PanelVocabTag $model): void
    {
        $this->syncFromModel($model);
    }

    public function deleted(LanguageActivity|LanguageActivityWord|ContentTranslation|PanelVocabTag $model): void
    {
        $this->syncFromModel($model);
    }

    private function syncFromModel(LanguageActivity|LanguageActivityWord|ContentTranslation|PanelVocabTag $model): void
    {
        if ($model instanceof LanguageActivity) {
            $this->coverage->syncLanguageRegistryWithStatus($model->language_code);

            return;
        }

        if ($model instanceof LanguageActivityWord) {
            $code = $model->relationLoaded('activity')
                ? $model->activity?->language_code
                : LanguageActivity::query()->whereKey($model->language_activity_id)->value('language_code');

            if (is_string($code) && $code !== '') {
                $this->coverage->syncLanguageRegistryWithStatus($code);
            }

            return;
        }

        // Panel vocab tags do not map to languages.translation_coverage; no-op.
    }
}
