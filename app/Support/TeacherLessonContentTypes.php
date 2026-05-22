<?php

namespace App\Support;

use App\Models\OrganisationContentDecision;
use App\Models\User;
use App\Services\OrganisationModuleResolver;

/**
 * Lesson planner content types (12) aligned with org-admin published library.
 */
final class TeacherLessonContentTypes
{
    /** @return list<array{type: string, label: string, icon: string}> */
    public static function optionsFor(User $user): array
    {
        $resolver = app(OrganisationModuleResolver::class);
        $orgId = (int) ($user->organisation_id ?? 0);
        $out = [];

        foreach (CmsAdminContentNav::items() as $item) {
            $type = (string) $item['type'];
            if ($orgId > 0 && ! $resolver->isContentTypeAllowedForOrganisation($orgId, $type)) {
                continue;
            }
            $out[] = [
                'type' => $type,
                'label' => (string) $item['label'],
                'icon' => (string) $item['icon'],
            ];
        }

        return $out;
    }

    public static function usesComicPicker(string $contentType): bool
    {
        return $contentType === OrganisationContentDecision::TYPE_STORY;
    }

    public static function usesSongPicker(string $contentType): bool
    {
        return $contentType === OrganisationContentDecision::TYPE_SONG;
    }

    public static function usesActivityPicker(string $contentType): bool
    {
        return ! self::usesComicPicker($contentType) && ! self::usesSongPicker($contentType);
    }

    /**
     * Legacy {@see \App\Models\Activity} `type` for lesson content types that use the activities table.
     */
    public static function activityTypeForContentType(string $contentType): ?string
    {
        return match ($contentType) {
            OrganisationContentDecision::TYPE_FLASHCARD => 'flashcard',
            OrganisationContentDecision::TYPE_PUZZLE => 'puzzle',
            OrganisationContentDecision::TYPE_DRAWING,
            OrganisationContentDecision::TYPE_COLOURING => 'drawing_kit',
            OrganisationContentDecision::TYPE_LANGUAGE => 'vocab_pack',
            OrganisationContentDecision::TYPE_GAME => 'game',
            OrganisationContentDecision::TYPE_MAZE => 'maze',
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => 'spot_difference',
            OrganisationContentDecision::TYPE_WORD_SEARCH => 'word_search',
            OrganisationContentDecision::TYPE_CULTURE => 'culture',
            default => null,
        };
    }

    /** @return list<string> */
    public static function allowedContentTypes(): array
    {
        return OrganisationContentDecision::ALL_TYPES;
    }
}
