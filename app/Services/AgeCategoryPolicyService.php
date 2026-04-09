<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\AgeProfile;
use App\Models\ChildProfile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AgeCategoryPolicyService
{
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

    public function applyContentPolicies(?AgeProfile $category, Builder $comics, Builder $songs, Builder $activities): void
    {
        if (! $category) {
            return;
        }

        $min = $category->min_age;
        $max = $category->max_age;

        $this->applyAgeWindow($comics, 'age_min', 'age_max', $min, $max);
        $this->applyAgeWindow($songs, 'age_min', 'age_max', $min, $max);

        $allowedModules = data_get($category->content_access_rules, 'modules', []);
        if (is_array($allowedModules) && count($allowedModules) > 0) {
            $activities->whereIn('type', $allowedModules);
        }
    }

    public function enrichUiPolicyPayload(?AgeProfile $category): array
    {
        if (! $category) {
            return [];
        }

        return [
            'age_profile' => $category->only([
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
            ]),
            'ui_features' => $category->ui_features ?? [],
            'content_access_rules' => $category->content_access_rules ?? [],
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
