<?php

namespace App\Services;

use App\Models\AgeProfile;
use App\Models\ChildProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AgeCategoryPolicyService
{
    public function __construct(
        private readonly OrganisationModuleResolver $moduleResolver,
    ) {}
    public function resolveForChild(ChildProfile $child): ?AgeProfile
    {
        if ($child->ageProfile) {
            return $child->ageProfile;
        }

        $age = Carbon::parse($child->dob)->age;

        return $this->resolveForAge($age);
    }

    public function resolveForAge(int $age): ?AgeProfile
    {
        return AgeProfile::query()
            ->where('is_active', true)
            ->where('min_age', '<=', $age)
            ->where(function (Builder $query) use ($age): void {
                $query->whereNull('max_age')->orWhere('max_age', '>=', $age);
            })
            ->orderByDesc('min_age')
            ->first();
    }

    public function applyContentPolicies(
        ?AgeProfile $category,
        Builder $comics,
        Builder $songs,
        Builder $activities,
        ?User $user = null,
    ): void {
        if (! $category) {
            return;
        }

        $min = $category->min_age;
        $max = $category->max_age;

        $this->applyAgeWindow($comics, 'age_min', 'age_max', $min, $max);
        $this->applyAgeWindow($songs, 'age_min', 'age_max', $min, $max);

        $ageModules = data_get($category->content_access_rules, 'modules', []);
        if (! is_array($ageModules) || $ageModules === []) {
            return;
        }

        $effectiveAgeModules = $this->moduleResolver->effectiveAgeProfileModulesForUser($user, $ageModules);

        if (! in_array('stories', $effectiveAgeModules, true)) {
            $comics->whereRaw('0 = 1');
        }

        if (! in_array('songs', $effectiveAgeModules, true)) {
            $songs->whereRaw('0 = 1');
        }

        $activityTypes = $this->moduleResolver->effectiveActivityTypesForAgeProfileModules($user, $ageModules);

        if ($activityTypes === []) {
            $activities->whereRaw('0 = 1');
        } else {
            $activities->whereIn('type', $activityTypes);
        }
    }

    public function enrichUiPolicyPayload(?AgeProfile $category, ?User $user = null): array
    {
        if (! $category) {
            return [];
        }

        $formatted = $this->moduleResolver->formatAgeProfileForApi($category, $user);

        return [
            'age_profile' => collect($formatted)->only([
                'id',
                'name',
                'key',
                'min_age',
                'max_age',
                'ui_scale',
                'touch_target_px',
                'reading_level',
                'activity_complexity',
                'is_audio_first',
            ])->all(),
            'ui_features' => $formatted['ui_features'] ?? [],
            'content_access_rules' => $formatted['content_access_rules'] ?? [],
        ];
    }

    private function applyAgeWindow(Builder $query, string $minColumn, string $maxColumn, int $ageMin, ?int $ageMax): void
    {
        $query->where(function (Builder $inner) use ($minColumn, $maxColumn, $ageMin, $ageMax): void {
            $inner->whereNull($minColumn)
                ->orWhere($minColumn, '<=', $ageMax ?? $ageMin);
        });

        $query->where(function (Builder $inner) use ($maxColumn, $ageMin): void {
            $inner->whereNull($maxColumn)
                ->orWhere($maxColumn, '>=', $ageMin);
        });
    }
}
