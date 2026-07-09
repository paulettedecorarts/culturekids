<?php

namespace App\Support;

use Illuminate\Support\Str;
use InvalidArgumentException;

class ChildFriendlyFontLibrary
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return config('child-friendly-fonts.fonts', []);
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->all());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function forRole(string $role): array
    {
        return collect($this->all())
            ->filter(fn (array $font) => in_array($role, $font['roles'] ?? [], true))
            ->all();
    }

    public function defaultKey(string $role): string
    {
        $key = config("child-friendly-fonts.defaults.{$role}");

        if (! is_string($key) || ! $this->exists($key)) {
            throw new InvalidArgumentException("Unknown default font role [{$role}].");
        }

        return $key;
    }

    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    /**
     * Resolve a stored key or legacy Google Font family name to a canonical key.
     */
    public function resolveKey(?string $value, string $role = 'body'): string
    {
        $value = trim((string) $value);

        if ($value !== '' && $this->exists($value)) {
            return $value;
        }

        if ($value !== '') {
            $legacyKey = $this->legacyKeyForFamily($value);

            if ($legacyKey !== null) {
                return $legacyKey;
            }
        }

        $default = $this->defaultKey($role);
        $font = $this->all()[$default] ?? null;

        if (is_array($font) && in_array($role, $font['roles'] ?? [], true)) {
            return $default;
        }

        $firstForRole = array_key_first($this->forRole($role));

        return is_string($firstForRole) ? $firstForRole : $default;
    }

    public function label(string $key): string
    {
        return (string) ($this->font($key)['label'] ?? $key);
    }

    public function family(string $key): string
    {
        return (string) ($this->font($key)['family'] ?? $key);
    }

    public function cssFamilyStack(string $key): string
    {
        $font = $this->font($key);
        $family = (string) ($font['family'] ?? $key);
        $fallback = (string) ($font['fallback'] ?? 'sans-serif');

        return "'{$family}', {$fallback}, system-ui, sans-serif";
    }

    /**
     * @param  list<string>  $keys
     */
    public function googleFontsStylesheetUrl(array $keys): string
    {
        $families = [];

        foreach (array_unique(array_filter($keys)) as $key) {
            if (! $this->exists($key)) {
                continue;
            }

            $font = $this->font($key);
            $google = (string) ($font['google'] ?? '');
            $weights = (string) ($font['weights'] ?? '400;600;700');

            if ($google === '') {
                continue;
            }

            $families[] = "family={$google}:wght@{$weights}";
        }

        if ($families === []) {
            $families[] = 'family=Baloo+2:wght@400;600;700;800';
        }

        return 'https://fonts.googleapis.com/css2?'.implode('&', $families).'&display=swap';
    }

    /**
     * @return array<string, mixed>
     */
    protected function font(string $key): array
    {
        $font = $this->all()[$key] ?? null;

        if (! is_array($font)) {
            throw new InvalidArgumentException("Unknown child-friendly font key [{$key}].");
        }

        return $font;
    }

    protected function legacyKeyForFamily(string $value): ?string
    {
        $needle = Str::lower(Str::squish($value));

        foreach ($this->all() as $key => $font) {
            $family = Str::lower(Str::squish((string) ($font['family'] ?? '')));
            $label = Str::lower(Str::squish((string) ($font['label'] ?? '')));

            if ($needle === $family || $needle === $label || $needle === Str::lower($key)) {
                return $key;
            }
        }

        // Common legacy values saved before the font library existed.
        return match ($needle) {
            'inter' => $this->exists('nunito') ? 'nunito' : null,
            default => null,
        };
    }
}
