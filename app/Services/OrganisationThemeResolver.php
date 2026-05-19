<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\Theme;
use App\Models\User;

class OrganisationThemeResolver
{
    /**
     * Resolve the effective theme for a mobile user (parent, teacher, etc.).
     *
     * @return array{
     *     source: string,
     *     organisation_id: int|null,
     *     theme_id: int|null,
     *     name: string,
     *     slug: string|null,
     *     logo_url: string|null,
     *     colors: array<string, string>,
     *     typography: array<string, mixed>|null,
     *     spacing: array<string, mixed>|null,
     *     borders: array<string, mixed>|null,
     *     metadata: array<string, mixed>,
     * }
     */
    public function resolveForUser(?User $user): array
    {
        $organisation = $user?->organisation_id
            ? Organisation::query()->find($user->organisation_id)
            : null;

        return $this->resolveForOrganisation($organisation);
    }

    /**
     * @return array{
     *     source: string,
     *     organisation_id: int|null,
     *     theme_id: int|null,
     *     name: string,
     *     slug: string|null,
     *     logo_url: string|null,
     *     colors: array<string, string>,
     *     typography: array<string, mixed>|null,
     *     spacing: array<string, mixed>|null,
     *     borders: array<string, mixed>|null,
     *     metadata: array<string, mixed>,
     * }
     */
    public function resolveForOrganisation(?Organisation $organisation): array
    {
        $themeRecord = $this->resolveThemeRecord($organisation);
        $base = $this->basePayload($themeRecord, $organisation);

        if ($organisation?->theme && is_array($organisation->theme)) {
            $base = $this->mergeThemeArrays($base, $organisation->theme);
            $base['source'] = 'organisation_override';
        }

        return $base;
    }

    protected function resolveThemeRecord(?Organisation $organisation): ?Theme
    {
        $orgId = $organisation?->id;

        if ($orgId) {
            $orgTheme = Theme::query()
                ->where('org_id', $orgId)
                ->where('is_active', true)
                ->where('is_default', true)
                ->first();

            if ($orgTheme) {
                return $orgTheme;
            }

            return Theme::query()
                ->where('org_id', $orgId)
                ->where('is_active', true)
                ->orderByDesc('updated_at')
                ->first();
        }

        return Theme::query()
            ->whereNull('org_id')
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    /**
     * @return array{
     *     source: string,
     *     organisation_id: int|null,
     *     theme_id: int|null,
     *     name: string,
     *     slug: string|null,
     *     logo_url: string|null,
     *     colors: array<string, string>,
     *     typography: array<string, mixed>|null,
     *     spacing: array<string, mixed>|null,
     *     borders: array<string, mixed>|null,
     *     metadata: array<string, mixed>,
     * }
     */
    protected function basePayload(?Theme $themeRecord, ?Organisation $organisation): array
    {
        $defaultColors = Theme::defaultColors();

        if ($themeRecord) {
            return [
                'source' => $organisation ? 'organisation_theme' : 'platform_theme',
                'organisation_id' => $organisation?->id,
                'theme_id' => $themeRecord->id,
                'name' => $themeRecord->name,
                'slug' => $themeRecord->slug,
                'logo_url' => $organisation?->logo_url,
                'colors' => array_merge($defaultColors, $themeRecord->colors ?? []),
                'typography' => $themeRecord->typography,
                'spacing' => $themeRecord->spacing,
                'borders' => $themeRecord->borders,
                'metadata' => $themeRecord->metadata ?? [],
            ];
        }

        return [
            'source' => 'platform_default',
            'organisation_id' => $organisation?->id,
            'theme_id' => null,
            'name' => 'Culture Kids Default',
            'slug' => null,
            'logo_url' => $organisation?->logo_url,
            'colors' => $defaultColors,
            'typography' => null,
            'spacing' => null,
            'borders' => null,
            'metadata' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    protected function mergeThemeArrays(array $base, array $override): array
    {
        foreach (['colors', 'typography', 'spacing', 'borders', 'metadata'] as $key) {
            if (! isset($override[$key]) || ! is_array($override[$key])) {
                continue;
            }

            $existing = $base[$key] ?? [];
            $base[$key] = is_array($existing)
                ? array_replace_recursive($existing, $override[$key])
                : $override[$key];
        }

        foreach (['name', 'slug', 'logo_url'] as $key) {
            if (array_key_exists($key, $override) && $override[$key] !== null && $override[$key] !== '') {
                $base[$key] = $override[$key];
            }
        }

        return $base;
    }
}
