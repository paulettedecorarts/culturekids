<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Comic;
use App\Models\Tribe;
use App\Services\PlatformLandingService;
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
    public string $font_heading = 'Baloo 2';
    public string $font_body = 'Inter';

    public ?string $peoples_section_title = null;
    public int $peoples_count = 65;
    /** @var list<int> */
    public array $featured_tribe_ids = [];

    public string $cta_title = '';
    public string $cta_subtitle = '';
    public ?string $seo_title = null;
    public ?string $seo_description = null;

    public function mount(PlatformLandingService $landing): void
    {
        $this->fillFrom($landing->draft());
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
            'font_heading' => ['nullable', 'string', 'max:80'],
            'font_body' => ['nullable', 'string', 'max:80'],
            'peoples_section_title' => ['nullable', 'string', 'max:180'],
            'peoples_count' => ['required', 'integer', 'min:1', 'max:200'],
            'featured_tribe_ids' => ['array'],
            'featured_tribe_ids.*' => ['integer', 'exists:tribes,id'],
            'cta_title' => ['nullable', 'string', 'max:200'],
            'cta_subtitle' => ['nullable', 'string', 'max:400'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:400'],
        ];
    }

    public function saveDraft(PlatformLandingService $landing): void
    {
        $this->validate();
        $this->persistUpload();
        $landing->saveDraft($this->payload());
        session()->flash('message', 'Landing page draft saved.');
    }

    public function publishLive(PlatformLandingService $landing): void
    {
        $this->validate();
        $this->persistUpload();
        $landing->saveDraft($this->payload());
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
    protected function payload(): array
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
        $this->font_heading = (string) ($data['font_heading'] ?? 'Baloo 2');
        $this->font_body = (string) ($data['font_body'] ?? 'Inter');
        $this->peoples_section_title = $data['peoples_section_title'] ?? null;
        $this->peoples_count = (int) ($data['peoples_count'] ?? 65);
        $this->featured_tribe_ids = array_values(array_map('intval', $data['featured_tribe_ids'] ?? []));
        $this->cta_title = (string) ($data['cta_title'] ?? '');
        $this->cta_subtitle = (string) ($data['cta_subtitle'] ?? '');
        $this->seo_title = $data['seo_title'] ?? null;
        $this->seo_description = $data['seo_description'] ?? null;
        $this->hero_image_upload = null;
    }

    public function render(PlatformLandingService $landing)
    {
        $preview = $landing->viewData();
        $preview['landing'] = array_merge($preview['landing'], $this->payload());

        return view('livewire.admin.landing-page-editor', [
            'comics' => Comic::query()->published()->orderBy('title')->get(['id', 'title']),
            'peoples' => Tribe::query()->orderBy('name')->get(['id', 'name', 'hero_emoji']),
            'previewUrl' => url('/'),
            'heroPreviewUrl' => $landing->viewData()['heroImageUrl'] ?? null,
        ])->layout('layouts.admin');
    }
}
