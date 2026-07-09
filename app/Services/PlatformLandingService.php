<?php

namespace App\Services;

use App\Models\Comic;
use App\Models\PlatformLandingSetting;
use App\Models\Tribe;
use App\Support\ChildFriendlyFontLibrary;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlatformLandingService
{
    public function __construct(
        protected ChildFriendlyFontLibrary $fonts,
    ) {}
    public function defaults(): array
    {
        return [
            'hero_headline' => 'Bring',
            'hero_highlight' => "Africa's Stories",
            'hero_headline_suffix' => 'to Life',
            'hero_subtitle' => 'Interactive cultural comics, songs, and language learning for children ages 2–6 — celebrating Uganda\'s peoples and heritage.',
            'hero_comic_id' => null,
            'hero_image_path' => null,
            'primary_color' => '#C44B2B',
            'secondary_color' => '#1E2D4A',
            'accent_color' => '#F2CB5A',
            'hero_bg_start' => '#FFF8F0',
            'hero_bg_end' => '#E8F4FC',
            'font_heading' => $this->fonts->defaultKey('heading'),
            'font_body' => $this->fonts->defaultKey('body'),
            'peoples_section_title' => null,
            'peoples_count' => 65,
            'featured_tribe_ids' => [],
            'cta_title' => 'Ready to start your child\'s cultural journey?',
            'cta_subtitle' => 'Join thousands of children learning about their heritage through stories and songs.',
            'seo_title' => null,
            'seo_description' => null,
            'pricing_section_title' => 'Plans that scale with you',
            'pricing_section_lead' => 'From families exploring at home to districts rolling out culturally grounded learning — start free and upgrade when you are ready.',
            'pricing_plans' => $this->defaultPricingPlans(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function defaultPricingPlans(): array
    {
        return [
            [
                'id' => 'plan-free',
                'name' => 'Free',
                'price_display' => '$0',
                'price_suffix' => '',
                'note' => 'For parents & trial classrooms',
                'features' => [
                    'Sample story packs & songs',
                    'One child profile',
                    'Platform default branding',
                    'Community support',
                ],
                'is_featured' => false,
                'badge' => null,
                'cta_label' => 'Get started',
                'cta_href' => '',
                'cta_style' => 'outline',
                'sort_order' => 0,
                'is_visible' => true,
            ],
            [
                'id' => 'plan-school',
                'name' => 'School',
                'price_display' => 'Custom',
                'price_suffix' => '/ year',
                'note' => 'Per organisation · modular add-ons',
                'features' => [
                    'Full people & story catalogue',
                    'Org admin & teacher portals',
                    'Review queue & approvals',
                    'Offline bundles & kiosk mode',
                    'Custom themes & modules',
                ],
                'is_featured' => true,
                'badge' => 'Most popular',
                'cta_label' => 'Start free trial',
                'cta_href' => '',
                'cta_style' => 'primary',
                'sort_order' => 1,
                'is_visible' => true,
            ],
            [
                'id' => 'plan-enterprise',
                'name' => 'Enterprise',
                'price_display' => "Let's talk",
                'price_suffix' => '',
                'note' => 'Districts, NGOs & multi-site rollouts',
                'features' => [
                    'Everything in School',
                    'Multi-organisation management',
                    'Priority onboarding & training',
                    'SLA & dedicated support',
                    'API & integration options',
                ],
                'is_featured' => false,
                'badge' => null,
                'cta_label' => 'Contact sales',
                'cta_href' => '',
                'cta_style' => 'outline',
                'sort_order' => 2,
                'is_visible' => true,
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $plans
     * @return list<array<string, mixed>>
     */
    public function normalizePricingPlans(array $plans): array
    {
        $normalized = collect($plans)
            ->map(function ($plan, $index) {
                $features = $plan['features'] ?? [];
                if (isset($plan['features_text']) && is_string($plan['features_text'])) {
                    $features = preg_split('/\r\n|\r|\n/', $plan['features_text']) ?: [];
                }
                $features = array_values(array_filter(array_map('trim', is_array($features) ? $features : [])));

                $id = (string) ($plan['id'] ?? '');
                if ($id === '') {
                    $id = 'plan-'.Str::uuid();
                }

                return [
                    'id' => $id,
                    'name' => trim((string) ($plan['name'] ?? 'Plan')),
                    'price_display' => trim((string) ($plan['price_display'] ?? '')),
                    'price_suffix' => trim((string) ($plan['price_suffix'] ?? '')),
                    'note' => trim((string) ($plan['note'] ?? '')),
                    'features' => $features,
                    'is_featured' => (bool) ($plan['is_featured'] ?? false),
                    'badge' => ($badge = trim((string) ($plan['badge'] ?? ''))) !== '' ? $badge : null,
                    'cta_label' => trim((string) ($plan['cta_label'] ?? 'Get started')) ?: 'Get started',
                    'cta_href' => trim((string) ($plan['cta_href'] ?? '')),
                    'cta_style' => in_array($plan['cta_style'] ?? 'outline', ['primary', 'outline'], true)
                        ? $plan['cta_style']
                        : 'outline',
                    'sort_order' => (int) ($plan['sort_order'] ?? $index),
                    'is_visible' => (bool) ($plan['is_visible'] ?? true),
                ];
            })
            ->filter(fn (array $plan) => $plan['name'] !== '')
            ->sortBy('sort_order')
            ->values();

        $featuredId = $normalized->firstWhere('is_featured', true)['id'] ?? null;

        return $normalized
            ->map(fn (array $plan) => array_merge($plan, [
                'is_featured' => $featuredId !== null && $plan['id'] === $featuredId,
            ]))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function visiblePricingPlans(array $settings): array
    {
        $plans = is_array($settings['pricing_plans'] ?? null) ? $settings['pricing_plans'] : [];

        return collect($this->normalizePricingPlans($plans !== [] ? $plans : $this->defaultPricingPlans()))
            ->filter(fn (array $plan) => $plan['is_visible'])
            ->values()
            ->all();
    }

    public function resolvePlanCtaUrl(array $plan): string
    {
        $href = trim((string) ($plan['cta_href'] ?? ''));

        if ($href === '') {
            return route('register');
        }

        if (str_starts_with($href, '/') || str_starts_with($href, 'http')) {
            return $href;
        }

        return route('register');
    }

    public function draft(): array
    {
        $row = PlatformLandingSetting::instance();
        $draft = is_array($row->draft) ? $row->draft : [];

        return $this->normalizeSettings(array_merge($this->defaults(), $draft));
    }

    public function published(): array
    {
        $row = PlatformLandingSetting::instance();
        $published = is_array($row->published) && $row->published !== []
            ? $row->published
            : null;

        return $this->normalizeSettings(array_merge($this->defaults(), $published ?? $this->draft()));
    }

    public function saveDraft(array $data): void
    {
        $row = PlatformLandingSetting::instance();
        $row->update(['draft' => $this->normalizeSettings(array_merge($this->draft(), $data))]);
    }

    public function publish(int $userId): void
    {
        $row = PlatformLandingSetting::instance();
        $draft = $this->draft();
        $row->update([
            'published' => $draft,
            'published_at' => now(),
            'published_by' => $userId,
        ]);
    }

    public function discardDraft(): void
    {
        $row = PlatformLandingSetting::instance();
        $published = is_array($row->published) ? $row->published : [];

        $row->update(['draft' => $this->normalizeSettings($published !== [] ? $published : $this->defaults())]);
    }

    /**
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $settings = $this->published();
        $heroComic = $this->resolveHeroComic($settings);
        $heroImageUrl = $this->resolveHeroImageUrl($settings, $heroComic);
        $featuredPeoples = $this->featuredPeoples($settings);
        $peoplesCount = (int) ($settings['peoples_count'] ?? 65);

        $headingKey = $this->fonts->resolveKey($settings['font_heading'] ?? null, 'heading');
        $bodyKey = $this->fonts->resolveKey($settings['font_body'] ?? null, 'body');

        return [
            'landing' => $settings,
            'landingFonts' => [
                'heading_key' => $headingKey,
                'body_key' => $bodyKey,
                'heading_stack' => $this->fonts->cssFamilyStack($headingKey),
                'body_stack' => $this->fonts->cssFamilyStack($bodyKey),
                'stylesheet_url' => $this->fonts->googleFontsStylesheetUrl([$headingKey, $bodyKey]),
            ],
            'heroComic' => $heroComic,
            'heroImageUrl' => $heroImageUrl,
            'featuredPeoples' => $featuredPeoples,
            'peoplesCount' => $peoplesCount,
            'peoplesSectionTitle' => $settings['peoples_section_title']
                ?: heritage('explore_peoples_count', ['count' => $peoplesCount]),
            'seoTitle' => $settings['seo_title'] ?: config('app.name'),
            'seoDescription' => $settings['seo_description']
                ?: 'Interactive cultural comics, songs, and language learning for Ugandan children.',
            'pricingSectionTitle' => $settings['pricing_section_title'] ?? 'Plans that scale with you',
            'pricingSectionLead' => $settings['pricing_section_lead'] ?? '',
            'pricingPlans' => collect($this->visiblePricingPlans($settings))
                ->map(fn (array $plan) => array_merge($plan, [
                    'cta_url' => $this->resolvePlanCtaUrl($plan),
                ]))
                ->all(),
        ];
    }

    protected function resolveHeroComic(array $settings): ?Comic
    {
        $id = $settings['hero_comic_id'] ?? null;

        if ($id) {
            $comic = Comic::query()->published()->with(['tribe:id,name,hero_emoji', 'panels'])->find($id);

            return $comic;
        }

        return Comic::query()
            ->published()
            ->where(function ($q) {
                $q->whereNotNull('cover_image_path')
                    ->orWhereHas('panels', fn ($p) => $p->whereNotNull('image_path'));
            })
            ->with(['tribe:id,name,hero_emoji', 'panels'])
            ->latest()
            ->first();
    }

    protected function resolveHeroImageUrl(array $settings, ?Comic $comic): ?string
    {
        if (! empty($settings['hero_image_path'])) {
            return Storage::disk('public')->url($settings['hero_image_path']);
        }

        if (! $comic) {
            return null;
        }

        if ($comic->cover_image_path) {
            return Storage::disk('public')->url($comic->cover_image_path);
        }

        $panel = $comic->panels->firstWhere('image_path', '!=', null);

        return $panel?->image_path
            ? Storage::disk('public')->url($panel->image_path)
            : null;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Tribe>
     */
    protected function featuredPeoples(array $settings)
    {
        $ids = collect($settings['featured_tribe_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $query = Tribe::query()->orderBy('name');

        if ($ids->isNotEmpty()) {
            $query->whereIn('id', $ids->all());

            return $query->get()->sortBy(fn (Tribe $t) => $ids->search($t->id))->values();
        }

        return $query->limit(7)->get();
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    protected function normalizeSettings(array $settings): array
    {
        $settings['font_heading'] = $this->fonts->resolveKey($settings['font_heading'] ?? null, 'heading');
        $settings['font_body'] = $this->fonts->resolveKey($settings['font_body'] ?? null, 'body');

        return $settings;
    }
}
