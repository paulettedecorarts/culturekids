<?php

namespace App\Services;

use App\Models\Language;
use App\Models\LanguageActivity;
use App\Models\LanguageActivityWord;
use App\Models\PanelVocabTag;

class TranslationCoverageService
{
    /**
     * Coverage for language-activity word rows (and sentence translations) for a registry code.
     */
    public function coveragePercentForLanguageCode(string $code): int
    {
        $code = trim($code);
        if ($code === '') {
            return 0;
        }

        $activityIds = LanguageActivity::query()
            ->where('language_code', $code)
            ->pluck('id');

        if ($activityIds->isEmpty()) {
            return 0;
        }

        $wordStats = LanguageActivityWord::query()
            ->whereIn('language_activity_id', $activityIds)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN translation IS NOT NULL AND TRIM(translation) != '' THEN 1 ELSE 0 END) as translated")
            ->first();

        $total = (int) ($wordStats->total ?? 0);
        $translated = (int) ($wordStats->translated ?? 0);

        $sentenceSlots = LanguageActivity::query()
            ->whereIn('id', $activityIds)
            ->whereIn('activity_type', ['proverb_jumble', 'sentence_builder'])
            ->count();

        if ($sentenceSlots > 0) {
            $sentenceTranslated = LanguageActivity::query()
                ->whereIn('id', $activityIds)
                ->whereIn('activity_type', ['proverb_jumble', 'sentence_builder'])
                ->whereNotNull('sentence_translation')
                ->where('sentence_translation', '!=', '')
                ->count();

            $total += $sentenceSlots;
            $translated += $sentenceTranslated;
        }

        if ($total === 0) {
            return 0;
        }

        return (int) round(($translated / $total) * 100);
    }

    /**
     * Story panel vocabulary coverage (all orgs / global).
     */
    public function panelVocabCoveragePercent(?int $organisationId = null): int
    {
        $query = PanelVocabTag::query();

        if ($organisationId !== null) {
            $query->whereHas('panel.comic', fn ($q) => $q->where('org_id', $organisationId));
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            return 0;
        }

        $translated = (clone $query)
            ->whereNotNull('translation')
            ->where('translation', '!=', '')
            ->count();

        return (int) round(($translated / $total) * 100);
    }

    public function syncLanguageRegistry(string $code): void
    {
        $code = trim($code);
        if ($code === '') {
            return;
        }

        $coverage = $this->coveragePercentForLanguageCode($code);

        Language::query()
            ->where('code', $code)
            ->update([
                'translation_coverage' => $coverage,
                'updated_at' => now(),
            ]);
    }

    public function syncAllLanguages(): void
    {
        $codes = LanguageActivity::query()
            ->distinct()
            ->pluck('language_code')
            ->filter(fn ($code) => is_string($code) && trim($code) !== '');

        foreach ($codes as $code) {
            $this->syncLanguageRegistry($code);
        }
    }

    public function derivedStatus(int $coverage): string
    {
        return match (true) {
            $coverage >= 80 => 'verified',
            $coverage >= 40 => 'partial',
            default => 'pending',
        };
    }

    public function syncLanguageRegistryWithStatus(string $code): void
    {
        $code = trim($code);
        if ($code === '') {
            return;
        }

        $coverage = $this->coveragePercentForLanguageCode($code);

        Language::query()
            ->where('code', $code)
            ->update([
                'translation_coverage' => $coverage,
                'status' => $this->derivedStatus($coverage),
                'updated_at' => now(),
            ]);
    }
}
