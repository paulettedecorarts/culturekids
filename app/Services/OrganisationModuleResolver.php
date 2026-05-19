<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\AgeProfile;
use App\Models\Comic;
use App\Models\Module;
use App\Models\Organisation;
use App\Models\Song;
use App\Models\User;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrganisationModuleResolver
{
    /**
     * @return list<array{key: string, name: string, description: string, icon: string, sort_order: int}>
     */
    public static function canonicalDefinitions(): array
    {
        return config('modules.definitions', []);
    }

    /**
     * @return array<string, string>
     */
    public static function contentTypeToModuleKey(): array
    {
        return config('modules.content_types', []);
    }

    /**
     * @return array<string, string>
     */
    public static function activityTypeToModuleKey(): array
    {
        return config('modules.activity_types', []);
    }

    /**
     * @return array<string, string>
     */
    public static function ageProfileModuleToOrgModule(): array
    {
        return config('modules.age_profile_modules', []);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function ageProfileModuleToActivityTypes(): array
    {
        return config('modules.age_profile_activity_types', []);
    }

    public static function orgModuleKeyForAgeProfileModule(string $ageProfileModule): ?string
    {
        $map = self::ageProfileModuleToOrgModule();

        return $map[$ageProfileModule] ?? null;
    }

    /**
     * @return list<string>
     */
    public static function activityTypesForAgeProfileModule(string $ageProfileModule): array
    {
        $types = config('modules.age_profile_activity_types.'.$ageProfileModule);

        return is_array($types) ? array_values($types) : [];
    }

    public function isAgeProfileModuleAllowedForUser(?User $user, string $ageProfileModule): bool
    {
        $orgModuleKey = self::orgModuleKeyForAgeProfileModule($ageProfileModule);

        if ($orgModuleKey === null) {
            return true;
        }

        return $this->isEnabledForUser($user, $orgModuleKey);
    }

    /**
     * Age-profile module strings that pass the organisation licence gate.
     *
     * @param  list<string>  $ageProfileModules
     * @return list<string>
     */
    public function effectiveAgeProfileModulesForUser(?User $user, array $ageProfileModules): array
    {
        return array_values(array_filter(
            $ageProfileModules,
            fn (string $module) => $this->isAgeProfileModuleAllowedForUser($user, $module)
        ));
    }

    /**
     * Activity `type` values allowed for a child after age rules + org modules.
     *
     * @param  list<string>  $ageProfileModules
     * @return list<string>
     */
    public function effectiveActivityTypesForAgeProfileModules(?User $user, array $ageProfileModules): array
    {
        $types = [];

        foreach ($this->effectiveAgeProfileModulesForUser($user, $ageProfileModules) as $module) {
            foreach (self::activityTypesForAgeProfileModule($module) as $type) {
                $types[] = $type;
            }
        }

        return array_values(array_unique($types));
    }

    /**
     * @return array<string, mixed>
     */
    public function formatAgeProfileForApi(AgeProfile $profile, ?User $user): array
    {
        $rules = is_array($profile->content_access_rules) ? $profile->content_access_rules : [];
        $modules = is_array($rules['modules'] ?? null) ? array_values($rules['modules']) : [];

        $effectiveModules = $this->effectiveAgeProfileModulesForUser($user, $modules);
        $effectiveOrgKeys = [];

        foreach ($effectiveModules as $ageModule) {
            $orgKey = self::orgModuleKeyForAgeProfileModule($ageModule);

            if ($orgKey !== null) {
                $effectiveOrgKeys[] = $orgKey;
            }
        }

        $rules['modules'] = $modules;
        $rules['effective_modules'] = $effectiveModules;
        $rules['effective_organisation_module_keys'] = array_values(array_unique($effectiveOrgKeys));
        $rules['effective_activity_types'] = $this->effectiveActivityTypesForAgeProfileModules($user, $modules);

        return [
            'id' => $profile->id,
            'name' => $profile->name,
            'key' => $profile->key,
            'min_age' => $profile->min_age,
            'max_age' => $profile->max_age,
            'icon_emoji' => $profile->icon_emoji,
            'color' => $profile->color,
            'ui_scale' => $profile->ui_scale,
            'touch_target_px' => $profile->touch_target_px,
            'reading_level' => $profile->reading_level,
            'activity_complexity' => $profile->activity_complexity,
            'content_access_rules' => $rules,
            'ui_features' => $profile->ui_features ?? [],
            'is_audio_first' => (bool) $profile->is_audio_first,
        ];
    }

    public static function moduleKeyForContentType(string $contentType): ?string
    {
        return self::contentTypeToModuleKey()[$contentType] ?? null;
    }

    public static function moduleKeyForActivityType(string $activityType): ?string
    {
        return self::activityTypeToModuleKey()[$activityType] ?? null;
    }

    /**
     * @return list<array{key: string, name: string, description: string|null, icon: string|null, enabled: bool, globally_enabled: bool, module_id: int|null}>
     */
    public function modulesForUser(?User $user): array
    {
        $organisation = $user?->organisation_id
            ? Organisation::query()->with('modules')->find($user->organisation_id)
            : null;

        $catalog = Module::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->keyBy('key');

        $out = [];

        foreach ($catalog as $module) {
            $out[] = $this->formatModuleRow($module, $organisation);
        }

        return $out;
    }

    public function isEnabledForUser(?User $user, string $moduleKey): bool
    {
        foreach ($this->modulesForUser($user) as $row) {
            if ($row['key'] === $moduleKey) {
                return $row['enabled'];
            }
        }

        return false;
    }

    public function assertEnabledForUser(?User $user, string $moduleKey): void
    {
        if ($this->isEnabledForUser($user, $moduleKey)) {
            return;
        }

        throw new HttpException(403, "The \"{$moduleKey}\" module is not enabled for this account.");
    }

    public function isContentTypeAllowed(?User $user, string $contentType): bool
    {
        if ($user?->organisation_id) {
            return $this->isContentTypeAllowedForOrganisation((int) $user->organisation_id, $contentType);
        }

        $moduleKey = self::moduleKeyForContentType($contentType);

        if ($moduleKey === null) {
            return true;
        }

        return $this->isEnabledForUser($user, $moduleKey);
    }

    public function isEnabledForOrganisation(int $organisationId, string $moduleKey): bool
    {
        $organisation = Organisation::query()->with('modules')->find($organisationId);

        if (! $organisation) {
            return false;
        }

        $module = Module::query()->where('key', $moduleKey)->first();

        if (! $module) {
            return false;
        }

        return $this->formatModuleRow($module, $organisation)['enabled'];
    }

    public function isContentTypeAllowedForOrganisation(int $organisationId, string $contentType): bool
    {
        $moduleKey = self::moduleKeyForContentType($contentType);

        if ($moduleKey === null) {
            return true;
        }

        return $this->isEnabledForOrganisation($organisationId, $moduleKey);
    }

    public function isActivityTypeAllowedForOrganisation(int $organisationId, string $activityType): bool
    {
        $moduleKey = self::moduleKeyForActivityType($activityType);

        if ($moduleKey === null) {
            return true;
        }

        return $this->isEnabledForOrganisation($organisationId, $moduleKey);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return Collection<int, array<string, mixed>>
     */
    public function filterReviewItemsForOrganisation(Collection $items, int $organisationId): Collection
    {
        return $items
            ->filter(function (array $item) use ($organisationId) {
                $contentType = (string) ($item['content_type'] ?? '');

                return $contentType === ''
                    || $this->isContentTypeAllowedForOrganisation($organisationId, $contentType);
            })
            ->values();
    }

    public function isActivityTypeAllowed(?User $user, string $activityType): bool
    {
        $moduleKey = self::moduleKeyForActivityType($activityType);

        if ($moduleKey === null) {
            return true;
        }

        return $this->isEnabledForUser($user, $moduleKey);
    }

    public function assertActivityTypeAllowedForUser(?User $user, string $activityType): void
    {
        $moduleKey = self::moduleKeyForActivityType($activityType);

        if ($moduleKey === null) {
            return;
        }

        $this->assertEnabledForUser($user, $moduleKey);
    }

    /**
     * @param  Collection<int, Comic>  $comics
     * @return Collection<int, Comic>
     */
    public function filterComicsForUser(Collection $comics, ?User $user): Collection
    {
        if (! $this->isEnabledForUser($user, 'stories')) {
            return collect();
        }

        return $comics->values();
    }

    /**
     * @param  Collection<int, Song>  $songs
     * @return Collection<int, Song>
     */
    public function filterSongsForUser(Collection $songs, ?User $user): Collection
    {
        if (! $this->isEnabledForUser($user, 'songs')) {
            return collect();
        }

        return $songs->values();
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @return Collection<int, Activity>
     */
    public function filterActivitiesForUser(Collection $activities, ?User $user): Collection
    {
        return $activities
            ->filter(fn (Activity $activity) => $this->isActivityTypeAllowed($user, $activity->type))
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    public function enabledKeysForUser(?User $user): Collection
    {
        return collect($this->modulesForUser($user))
            ->filter(fn (array $row) => $row['enabled'])
            ->pluck('key');
    }

    /**
     * @return array{key: string, name: string, description: string|null, icon: string|null, enabled: bool, globally_enabled: bool, module_id: int|null}
     */
    protected function formatModuleRow(Module $module, ?Organisation $organisation): array
    {
        $globallyEnabled = (bool) $module->is_enabled;
        $enabled = $globallyEnabled;

        if ($organisation && $globallyEnabled) {
            $attached = $organisation->modules->firstWhere('id', $module->id);

            if ($attached !== null) {
                $enabled = (bool) $attached->pivot->is_enabled;
            }
        }

        return [
            'key' => $module->key,
            'name' => $module->name,
            'description' => $module->description,
            'icon' => $module->icon,
            'enabled' => $enabled,
            'globally_enabled' => $globallyEnabled,
            'module_id' => $module->id,
        ];
    }
}
