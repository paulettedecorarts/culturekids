<?php

namespace App\Services;

use App\Models\Theme;
use App\Models\User;
use App\Support\ColorUtils;

class WebPortalThemeService
{
    public function __construct(
        private readonly OrganisationThemeResolver $themeResolver,
        private readonly OrganisationModuleResolver $moduleResolver,
    ) {}

    /**
     * Resolved theme payload plus light/dark CSS variables for portal pages.
     *
     * @return array{
     *     theme: array<string, mixed>,
     *     theme_engine_enabled: bool,
     *     css_variables: array<string, string>,
     *     css_variables_light: array<string, string>,
     *     css_variables_dark: array<string, string>,
     * }
     */
    public function forRequest(?User $user = null): array
    {
        $user ??= auth()->user();

        $themeEngineEnabled = $user !== null
            && $this->moduleResolver->isEnabledForUser($user, 'theme_engine');

        $theme = $themeEngineEnabled
            ? $this->themeResolver->resolveForUser($user)
            : $this->themeResolver->resolveForOrganisation(null);

        $colors = $theme['colors'] ?? Theme::defaultColors();
        $darkOverrides = $this->darkColorOverrides($theme);

        $light = $this->toLightVariables($colors);
        $dark = $this->toDarkVariables(array_merge($colors, $darkOverrides));

        return [
            'theme' => $theme,
            'theme_engine_enabled' => $themeEngineEnabled,
            'css_variables' => $light,
            'css_variables_light' => $light,
            'css_variables_dark' => $dark,
        ];
    }

    /**
     * @param  array<string, string>  $colors
     * @return array<string, string>
     */
    public function toCssVariables(array $colors): array
    {
        return $this->toLightVariables($colors);
    }

    /**
     * Light mode: org palette as-is (buttons, accents, cream backgrounds).
     *
     * @param  array<string, string>  $colors
     * @return array<string, string>
     */
    public function toLightVariables(array $colors): array
    {
        $colors = array_merge(Theme::defaultColors(), $colors);

        $primary = $colors['primary'];
        $secondary = $colors['secondary'];
        $accent = $colors['accent'];
        $success = $colors['success'];
        $background = $colors['background'];
        $danger = $colors['danger'];

        return [
            '--theme-primary' => $primary,
            '--theme-secondary' => $secondary,
            '--theme-accent' => $accent,
            '--theme-success' => $success,
            '--theme-danger' => $danger,
            '--theme-background' => $background,
            '--theme-surface' => $colors['surface'],
            '--theme-text' => $colors['text_primary'],
            '--theme-text-muted' => $colors['text_secondary'],
            '--clay-red' => $primary,
            '--clay-red-light' => ColorUtils::lighten($primary, 14),
            '--clay-red-dark' => $danger,
            '--sunfire' => $secondary,
            '--sunfire-light' => ColorUtils::lighten($secondary, 12),
            '--sunfire-pale' => ColorUtils::lighten($secondary, 42),
            '--savanna-gold' => $accent,
            '--savanna-light' => ColorUtils::lighten($accent, 22),
            '--banana-green' => $success,
            '--banana-mid' => ColorUtils::lighten($success, 28),
            '--banana-light' => ColorUtils::lighten($success, 48),
            '--leaf-pale' => ColorUtils::lighten($success, 55),
            '--ink' => $colors['text_primary'],
            '--ink-light' => $colors['text_secondary'],
            '--stone' => $colors['text_muted'],
            '--cream' => $background,
            '--cream-warm' => ColorUtils::darken($background, 4),
            '--cream-mid' => ColorUtils::darken($background, 10),
            '--white' => $colors['surface'],
        ];
    }

    /**
     * Dark mode: tinted surfaces from the org primary + slightly lifted brand accents for contrast.
     *
     * @param  array<string, string>  $colors
     * @return array<string, string>
     */
    public function toDarkVariables(array $colors): array
    {
        $colors = array_merge(Theme::defaultColors(), $colors);

        $primary = $colors['primary'];
        $secondary = $colors['secondary'];
        $accent = $colors['accent'];
        $success = $colors['success'];
        $danger = $colors['danger'];

        $darkBase = ColorUtils::mix('#0f1419', $primary, 10);
        $darkSurface = ColorUtils::mix('#161f2e', $primary, 8);
        $darkRaised = ColorUtils::lighten($darkSurface, 6);
        $darkHover = ColorUtils::lighten($darkSurface, 10);

        return [
            '--theme-primary' => ColorUtils::lighten($primary, 12),
            '--theme-secondary' => ColorUtils::lighten($secondary, 10),
            '--theme-accent' => ColorUtils::lighten($accent, 8),
            '--theme-success' => ColorUtils::lighten($success, 12),
            '--theme-danger' => ColorUtils::lighten($danger, 8),
            '--theme-background' => $darkBase,
            '--theme-surface' => $darkSurface,
            '--theme-text' => '#f3f4f6',
            '--theme-text-muted' => 'rgba(255,255,255,0.52)',
            '--clay-red' => ColorUtils::lighten($primary, 12),
            '--clay-red-light' => ColorUtils::lighten($primary, 24),
            '--clay-red-dark' => ColorUtils::lighten($danger, 6),
            '--sunfire' => ColorUtils::lighten($secondary, 10),
            '--sunfire-light' => ColorUtils::lighten($secondary, 20),
            '--sunfire-pale' => ColorUtils::mix($darkSurface, $secondary, 25),
            '--savanna-gold' => ColorUtils::lighten($accent, 6),
            '--savanna-light' => ColorUtils::lighten($accent, 18),
            '--banana-green' => ColorUtils::lighten($success, 14),
            '--banana-mid' => ColorUtils::lighten($success, 28),
            '--banana-light' => ColorUtils::lighten($success, 40),
            '--leaf-pale' => ColorUtils::mix($darkSurface, $success, 20),
            '--ink' => '#f3f4f6',
            '--ink-light' => 'rgba(255,255,255,0.72)',
            '--stone' => 'rgba(255,255,255,0.45)',
            '--cream' => $darkBase,
            '--cream-warm' => $darkRaised,
            '--cream-mid' => $darkHover,
            '--white' => $darkSurface,
        ];
    }

    /**
     * Optional per-mode overrides from theme metadata or organisation JSON.
     *
     * @param  array<string, mixed>  $theme
     * @return array<string, string>
     */
    protected function darkColorOverrides(array $theme): array
    {
        $metadata = is_array($theme['metadata'] ?? null) ? $theme['metadata'] : [];
        $fromMeta = $metadata['colors_dark'] ?? $metadata['dark_colors'] ?? [];

        return is_array($fromMeta) ? $fromMeta : [];
    }
}
