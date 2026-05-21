<?php

namespace App\Livewire\Concerns;

/**
 * Coerce dropdown / number input values to integers before validation.
 * Prevents Laravel "max:N" from treating values like "2 years" as string length.
 */
trait CoercesNumericFormFields
{
    public const CHILD_AGE_MIN = 1;

    public const CHILD_AGE_MAX = 18;

    /**
     * Override in components with custom age option lists (e.g. StoryForm).
     */
    protected function normalizeNumericFormFields(): void
    {
        if (property_exists($this, 'tribe_id')) {
            $this->tribe_id = self::coercePositiveInt($this->tribe_id);
        }

        if (property_exists($this, 'organisation_id')) {
            $this->organisation_id = self::coercePositiveInt($this->organisation_id);
        }

        if (property_exists($this, 'org_id')) {
            $this->org_id = self::coercePositiveInt($this->org_id);
        }

        if (property_exists($this, 'selectedOrgId')) {
            $this->selectedOrgId = self::coercePositiveInt($this->selectedOrgId);
        }

        if (property_exists($this, 'age_min')) {
            $this->age_min = $this->coerceChildAgeProperty($this->age_min, (int) ($this->age_min ?? 3));
        }

        if (property_exists($this, 'age_max')) {
            $this->age_max = $this->coerceChildAgeProperty($this->age_max, (int) ($this->age_max ?? 12));
        }

        if (property_exists($this, 'age_min') && property_exists($this, 'age_max')
            && $this->age_min !== null && $this->age_max !== null
            && (int) $this->age_max < (int) $this->age_min) {
            $this->age_max = min(self::CHILD_AGE_MAX, max((int) $this->age_min, (int) $this->age_max));
        }

        if (property_exists($this, 'star_points')) {
            $this->star_points = self::coerceIntInRange(
                $this->star_points,
                (int) ($this->star_points ?? 10),
                $this->starPointsMin(),
                $this->starPointsMax()
            );
        }

        if (property_exists($this, 'puzzle_pieces') && $this->puzzle_pieces !== null) {
            $this->puzzle_pieces = self::coerceIntInRange($this->puzzle_pieces, 12, 4, 400);
        }

        if (property_exists($this, 'sort_order')) {
            $this->sort_order = self::coerceIntInRange($this->sort_order, 100, 0, 99999);
        }

        if (property_exists($this, 'duration_seconds') && $this->duration_seconds !== null && $this->duration_seconds !== '') {
            $this->duration_seconds = self::coerceIntInRange($this->duration_seconds, 0, 0, 86400);
        }

        if (property_exists($this, 'vocab_words_count') && $this->vocab_words_count !== null && $this->vocab_words_count !== '') {
            $this->vocab_words_count = self::coerceIntInRange($this->vocab_words_count, 0, 0, 500);
        }

        if (property_exists($this, 'grid_size')) {
            $this->grid_size = self::coerceIntInRange($this->grid_size, 10, 5, 30);
        }
    }

    protected function starPointsMin(): int
    {
        return 0;
    }

    protected function starPointsMax(): int
    {
        return 100;
    }

    /**
     * @param  list<int>  $allowed
     */
    protected static function coerceAgeFromAllowed(mixed $value, array $allowed, int $default): int
    {
        if (is_string($value) && preg_match('/^\s*(\d+)/', $value, $matches)) {
            $value = (int) $matches[1];
        }

        $age = (int) $value;

        return in_array($age, $allowed, true) ? $age : $default;
    }

    protected function coerceChildAgeProperty(mixed $value, int $default): int
    {
        return self::coerceIntInRange($value, $default, self::CHILD_AGE_MIN, self::CHILD_AGE_MAX);
    }

    protected static function coercePositiveInt(mixed $value, ?int $default = null): ?int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_string($value) && preg_match('/^\s*(\d+)/', $value, $matches)) {
            $value = (int) $matches[1];
        }

        $int = (int) $value;

        return $int > 0 ? $int : $default;
    }

    protected static function coerceIntInRange(mixed $value, int $default, int $min, int $max): int
    {
        if (is_string($value) && preg_match('/^\s*(\d+)/', $value, $matches)) {
            $value = (int) $matches[1];
        }

        $int = (int) $value;

        if ($int < $min || $int > $max) {
            return max($min, min($max, $int !== 0 ? $int : $default));
        }

        return $int;
    }

    public function updatedTribeId(mixed $value): void
    {
        if (! property_exists($this, 'tribe_id')) {
            return;
        }

        $this->tribe_id = self::coercePositiveInt($value);
    }

    public function updatedAgeMin(mixed $value): void
    {
        if (! property_exists($this, 'age_min')) {
            return;
        }

        $this->age_min = $this->coerceChildAgeProperty($value, (int) ($this->age_min ?? 3));

        if (property_exists($this, 'age_max') && $this->age_max !== null && (int) $this->age_max < (int) $this->age_min) {
            $this->age_max = min(self::CHILD_AGE_MAX, max((int) $this->age_min, (int) $this->age_max));
        }
    }

    public function updatedAgeMax(mixed $value): void
    {
        if (! property_exists($this, 'age_max')) {
            return;
        }

        $this->age_max = $this->coerceChildAgeProperty($value, (int) ($this->age_max ?? 12));
    }

    public function updatedOrganisationId(mixed $value): void
    {
        if (! property_exists($this, 'organisation_id')) {
            return;
        }

        $this->organisation_id = self::coercePositiveInt($value);
    }

    public function updatedOrgId(mixed $value): void
    {
        if (! property_exists($this, 'org_id')) {
            return;
        }

        $this->org_id = self::coercePositiveInt($value);
    }
}
