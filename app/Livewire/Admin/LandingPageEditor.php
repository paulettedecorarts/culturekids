<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Comic;
use App\Models\Tribe;
use App\Services\PlatformLandingService;
use App\Support\ChildFriendlyFontLibrary;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class LandingPageEditor extends Component
{
    use WithFileUploads;

    public string $active_tab = 'hero';

    public string $hero_headline = '';
    public string $hero_highlight = '';
    public string $hero_headline_suffix = '';
    public string $hero_subtitle = '';
    public ?int $hero_comic_id = null;
    public ?string $hero_image_path = null;
    public $hero_image_upload = null;

    public string $primary_color = '#C44B2B';
    public string $secondary_color = '#1E2D4A';
    public string $accent_color = '#F2CB5A';
    public string $hero_bg_start = '#FFF8F0';
    public string $hero_bg_end = '#E8F4FC';
    public string $font_heading = 'chewy';
    public string $font_body = 'fredoka';

    public ?string $peoples_section_title = null;
    public int $peoples_count = 65;
    /** @var list<int> */
    public array $featured_tribe_ids = [];

    public string $cta_title = '';
    public string $cta_subtitle = '';
    public ?string $seo_title = null;
    public ?string $seo_description = null;

    public string $pricing_section_title = '';
    public string $pricing_section_lead = '';
    /** @var list<array<string, mixed>> */
    public array $pricing_plans = [];

    public function mount(PlatformLandingService $landing): void
    {
        $this->fillFrom($landing->draft());
    }

    protected function fontKeys(): array
    {
        return app(ChildFriendlyFontLibrary::class)->keys();
    }

    protected function rules(): array
    {
        return [
            'hero_headline' => ['nullable', 'string', 'max:120'],
            'hero_highlight' => ['nullable', 'string', 'max:120'],
            'hero_headline_suffix' => ['nullable', 'string', 'max:120'],
            'hero_subtitle' => ['nullable', 'string', 'max:600'],
            'hero_comic_id' => ['nullable', 'integer', 'exists:comics,id'],
            'hero_image_upload' => ['nullable', 'image', 'max:5120'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'hero_bg_start' => ['nullable', 'string', 'max:20'],
            'hero_bg_end' => ['nullable', 'string', 'max:20'],
            'font_heading' => ['nullable', 'string', Rule::in($this->fontKeys())],
            'font_body' => ['nullable', 'string', Rule::in($this->fontKeys())],
            'peoples_section_title' => ['nullable', 'string', 'max:180'],
            'peoples_count' => ['required', 'integer', 'min:1', 'max:200'],
            'featured_tribe_ids' => ['array'],
            'featured_tribe_ids.*' => ['integer', 'exists:tribes,id'],
            'cta_title' => ['nullable', 'string', 'max:200'],
            'cta_subtitle' => ['nullable', 'string', 'max:400'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:400'],
            'pricing_section_title' => ['nullable', 'string', 'max:200'],
            'pricing_section_lead' => ['nullable', 'string', 'max:600'],
            'pricing_plans' => ['array'],
            'pricing_plans.*.name' => ['nullable', 'string', 'max:80'],
            'pricing_plans.*.price_display' => ['nullable', 'string', 'max:80'],
            'pricing_plans.*.price_suffix' => ['nullable', 'string', 'max:40'],
            'pricing_plans.*.note' => ['nullable', 'string', 'max:200'],
            'pricing_plans.*.features_text' => ['nullable', 'string', 'max:2000'],
            'pricing_plans.*.badge' => ['nullable', 'string', 'max:60'],
            'pricing_plans.*.cta_label' => ['nullable', 'string', 'max:80'],
            'pricing_plans.*.cta_href' => ['nullable', 'string', 'max:500'],
            'pricing_plans.*.cta_style' => ['nullable', 'in:primary,outline'],
        ];
    }

    public function addPricingPlan(): void
    {
        $maxOrder = collect($this->pricing_plans)->max('sort_order') ?? -1;
        $this->pricing_plans[] = [
            'id' => 'plan-'.Str::uuid(),
            'name' => 'New plan',
            'price_display' => '',
            'price_suffix' => '',
            'note' => '',
            'features_text' => '',
            'is_featured' => false,
            'badge' => '',
            'cta_label' => 'Get started',
            'cta_href' => '',
            'cta_style' => 'outline',
            'sort_order' => $maxOrder + 1,
            'is_visible' => true,
        ];
    }

    public function removePricingPlan(string $planId): void
    {
        $this->pricing_plans = array_values(array_filter(
            $this->pricing_plans,
            fn (array $plan) => (string) ($plan['id'] ?? '') !== $planId
        ));
        $this->reindexPricingPlans();
    }

    public function movePricingPlanUp(string $planId): void
    {
        $this->swapPricingPlan($planId, -1);
    }

    public function movePricingPlanDown(string $planId): void
    {
        $this->swapPricingPlan($planId, 1);
    }

    public function setFeaturedPricingPlan(string $planId): void
    {
        foreach ($this->pricing_plans as $i => $plan) {
            $this->pricing_plans[$i]['is_featured'] = (string) ($plan['id'] ?? '') === $planId;
        }
    }

    protected function swapPricingPlan(string $planId, int $direction): void
    {
        $index = collect($this->pricing_plans)->search(
            fn (array $plan) => (string) ($plan['id'] ?? '') === $planId
        );

        if ($index === false) {
            return;
        }

        $target = $index + $direction;
        if ($target < 0 || $target >= count($this->pricing_plans)) {
            return;
        }

        $plans = $this->pricing_plans;
        [$plans[$index], $plans[$target]] = [$plans[$target], $plans[$index]];
        $this->pricing_plans = $plans;
        $this->reindexPricingPlans();
    }

    protected function reindexPricingPlans(): void
    {
        foreach ($this->pricing_plans as $i => $plan) {
            $this->pricing_plans[$i]['sort_order'] = $i;
        }
    }

    public function saveDraft(PlatformLandingService $landing): void
    {
        $this->validate();
        $this->persistUpload();
        $landing->saveDraft($this->payload($landing));
        session()->flash('message', 'Landing page draft saved.');
    }

    public function publishLive(PlatformLandingService $landing): void
    {
        $this->validate();
        $this->persistUpload();
        $landing->saveDraft($this->payload($landing));
        $landing->publish((int) auth()->id());
        AuditLog::record('PUBLISH_LANDING', 'platform/landing', []);
        session()->flash('message', 'Landing page published live.');
    }

    public function discardChanges(PlatformLandingService $landing): void
    {
        $landing->discardDraft();
        $this->fillFrom($landing->draft());
        session()->flash('message', 'Reverted to last published version.');
    }

    public function toggleFeaturedPeople(int $tribeId): void
    {
        if (in_array($tribeId, $this->featured_tribe_ids, true)) {
            $this->featured_tribe_ids = array_values(array_filter(
                $this->featured_tribe_ids,
                fn ($id) => (int) $id !== $tribeId
            ));

            return;
        }

        $this->featured_tribe_ids[] = $tribeId;
        $this->featured_tribe_ids = array_values(array_unique(array_map('intval', $this->featured_tribe_ids)));
    }

    protected function persistUpload(): void
    {
        if ($this->hero_image_upload) {
            $this->hero_image_path = $this->hero_image_upload->store('landing', 'public');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(PlatformLandingService $landing): array
    {
        return [
            'hero_headline' => $this->hero_headline,
            'hero_highlight' => $this->hero_highlight,
            'hero_headline_suffix' => $this->hero_headline_suffix,
            'hero_subtitle' => $this->hero_subtitle,
            'hero_comic_id' => $this->hero_comic_id,
            'hero_image_path' => $this->hero_image_path,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'accent_color' => $this->accent_color,
            'hero_bg_start' => $this->hero_bg_start,
            'hero_bg_end' => $this->hero_bg_end,
            'font_heading' => $this->font_heading,
            'font_body' => $this->font_body,
            'peoples_section_title' => $this->peoples_section_title,
            'peoples_count' => $this->peoples_count,
            'featured_tribe_ids' => $this->featured_tribe_ids,
            'cta_title' => $this->cta_title,
            'cta_subtitle' => $this->cta_subtitle,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'pricing_section_title' => $this->pricing_section_title,
            'pricing_section_lead' => $this->pricing_section_lead,
            'pricing_plans' => $landing->normalizePricingPlans($this->pricing_plans),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function fillFrom(array $data): void
    {
        $this->hero_headline = (string) ($data['hero_headline'] ?? '');
        $this->hero_highlight = (string) ($data['hero_highlight'] ?? '');
        $this->hero_headline_suffix = (string) ($data['hero_headline_suffix'] ?? '');
        $this->hero_subtitle = (string) ($data['hero_subtitle'] ?? '');
        $this->hero_comic_id = isset($data['hero_comic_id']) ? (int) $data['hero_comic_id'] : null;
        $this->hero_image_path = $data['hero_image_path'] ?? null;
        $this->primary_color = (string) ($data['primary_color'] ?? '#C44B2B');
        $this->secondary_color = (string) ($data['secondary_color'] ?? '#1E2D4A');
        $this->accent_color = (string) ($data['accent_color'] ?? '#F2CB5A');
        $this->hero_bg_start = (string) ($data['hero_bg_start'] ?? '#FFF8F0');
        $this->hero_bg_end = (string) ($data['hero_bg_end'] ?? '#E8F4FC');
        $this->font_heading = app(ChildFriendlyFontLibrary::class)->resolveKey($data['font_heading'] ?? null, 'heading');
        $this->font_body = app(ChildFriendlyFontLibrary::class)->resolveKey($data['font_body'] ?? null, 'body');
        $this->peoples_section_title = $data['peoples_section_title'] ?? null;
        $this->peoples_count = (int) ($data['peoples_count'] ?? 65);
        $this->featured_tribe_ids = array_values(array_map('intval', $data['featured_tribe_ids'] ?? []));
        $this->cta_title = (string) ($data['cta_title'] ?? '');
        $this->cta_subtitle = (string) ($data['cta_subtitle'] ?? '');
        $this->seo_title = $data['seo_title'] ?? null;
        $this->seo_description = $data['seo_description'] ?? null;
        $this->pricing_section_title = (string) ($data['pricing_section_title'] ?? 'Plans that scale with you');
        $this->pricing_section_lead = (string) ($data['pricing_section_lead'] ?? 'From families exploring at home to districts rolling out culturally grounded learning — start free and upgrade when you are ready.');
        $this->pricing_plans = $this->plansForEditor(
            is_array($data['pricing_plans'] ?? null) && $data['pricing_plans'] !== []
                ? $data['pricing_plans']
                : app(PlatformLandingService::class)->defaultPricingPlans()
        );
        $this->hero_image_upload = null;
    }

    /**
     * @param  list<array<string, mixed>>  $plans
     * @return list<array<string, mixed>>
     */
    protected function plansForEditor(array $plans): array
    {
        return collect($plans)
            ->sortBy('sort_order')
            ->values()
            ->map(function (array $plan) {
                $features = $plan['features'] ?? [];

                return array_merge($plan, [
                    'features_text' => is_array($features) ? implode("\n", $features) : '',
                    'badge' => $plan['badge'] ?? '',
                    'is_visible' => (bool) ($plan['is_visible'] ?? true),
                    'is_featured' => (bool) ($plan['is_featured'] ?? false),
                ]);
            })
            ->all();
    }

    public function render(PlatformLandingService $landing, ChildFriendlyFontLibrary $fonts)
    {
        $headingKey = $fonts->resolveKey($this->font_heading, 'heading');
        $bodyKey = $fonts->resolveKey($this->font_body, 'body');

        return view('livewire.admin.landing-page-editor', [
            'comics' => Comic::query()->published()->orderBy('title')->get(['id', 'title']),
            'peoples' => Tribe::query()->orderBy('name')->get(['id', 'name', 'hero_emoji']),
            'previewUrl' => url('/'),
            'previewPlans' => $landing->visiblePricingPlans(array_merge($landing->draft(), $this->payload($landing))),
            'headingFonts' => $fonts->forRole('heading'),
            'bodyFonts' => $fonts->forRole('body'),
            'previewFonts' => [
                'heading_stack' => $fonts->cssFamilyStack($headingKey),
                'body_stack' => $fonts->cssFamilyStack($bodyKey),
                'stylesheet_url' => $fonts->googleFontsStylesheetUrl([$headingKey, $bodyKey]),
            ],
        ])->layout('layouts.admin');
    }
}
