<?php

namespace App\Livewire\CMS;

use App\Models\AuditLog;
use App\Models\Organisation;
use App\Models\Tribe;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Site extends Component
{
    use WithFileUploads;

    public $organizationId;
    public $organization;
    public $organization_code;
    public $address;
    public $description;
    public $hero_headline;
    public $hero_subheadline;
    public $mission_text;
    public $contact_email;
    public $contact_phone;
    public $location_text;
    public $seo_title;
    public $seo_description;
    public $featured_tribe_ids = [];
    public $logo_path;
    public $logo_upload;
    public $is_published = false;
    public $active_tab = 'branding';

    protected function rules(): array
    {
        return [
            'organization' => ['required', 'string', 'max:120'],
            'organization_code' => ['required', 'alpha_dash', 'max:120', Rule::unique('organisations', 'code')->ignore($this->organizationId)],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'hero_headline' => ['nullable', 'string', 'max:180'],
            'hero_subheadline' => ['nullable', 'string', 'max:600'],
            'mission_text' => ['nullable', 'string', 'max:4000'],
            'contact_email' => ['nullable', 'email', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:60'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:400'],
            'featured_tribe_ids' => ['array'],
            'featured_tribe_ids.*' => ['integer', 'exists:tribes,id'],
            'logo_upload' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function loadFromOrganisation(Organisation $org): void
    {
        $settings = is_array($org->settings) ? $org->settings : [];
        $site = data_get($settings, 'site', []);

        $this->organizationId = $org->id;
        $this->organization = $org->name;
        $this->organization_code = $org->code;
        $this->address = $org->address;
        $this->description = $org->description;
        $this->logo_path = $org->logo_url;
        $this->hero_headline = (string) data_get($site, 'hero_headline', 'Heritage & Hope for Every Child');
        $this->hero_subheadline = (string) data_get($site, 'hero_subheadline', 'Connecting families with stories, songs, and values from our cultures.');
        $this->mission_text = (string) data_get($site, 'mission_text', $org->description ?? '');
        $this->contact_email = (string) data_get($site, 'contact_email', '');
        $this->contact_phone = (string) data_get($site, 'contact_phone', '');
        $this->location_text = (string) data_get($site, 'location_text', $org->address ?? '');
        $this->seo_title = (string) data_get($site, 'seo_title', "{$org->name} · African Cultural Heritage for Kids");
        $this->seo_description = (string) data_get($site, 'seo_description', "Discover your roots through stories and songs with {$org->name}.");
        $this->featured_tribe_ids = collect(data_get($site, 'featured_tribe_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();
        $this->is_published = (bool) data_get($site, 'is_published', false);
        $this->logo_upload = null;
    }

    public function mount()
    {
        $org = auth()->user()?->organisation;
        if (! $org) {
            return;
        }

        $this->loadFromOrganisation($org);
    }

    public function save(bool $publish = false): void
    {
        $org = auth()->user()?->organisation;
        if (! $org) {
            $this->addError('organization', 'No organization is linked to this account.');
            return;
        }

        $this->validate();

        if ($this->logo_upload) {
            $this->logo_path = $this->logo_upload->store('logos', 'public');
        }

        $settings = is_array($org->settings) ? $org->settings : [];
        $settings['site'] = [
            'hero_headline' => $this->hero_headline,
            'hero_subheadline' => $this->hero_subheadline,
            'mission_text' => $this->mission_text,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'location_text' => $this->location_text,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'featured_tribe_ids' => array_values(array_unique(array_map('intval', $this->featured_tribe_ids))),
            'is_published' => $publish ? true : $this->is_published,
            'published_at' => $publish ? now()->toIso8601String() : data_get($settings, 'site.published_at'),
        ];

        $org->update([
            'name' => $this->organization,
            'code' => $this->organization_code,
            'address' => $this->address,
            'description' => $this->description,
            'logo_url' => $this->logo_path,
            'settings' => $settings,
        ]);

        $this->loadFromOrganisation($org->fresh());
        AuditLog::record($publish ? 'PUBLISH_SITE' : 'UPDATE_SITE', "organisations/{$org->id}", [
            'org_code' => $org->code,
        ]);
        session()->flash('message', $publish ? 'Site published successfully.' : 'Site settings saved successfully.');
    }

    public function publishLive(): void
    {
        $this->save(true);
    }

    public function discardChanges(): void
    {
        $org = auth()->user()?->organisation;
        if (! $org) {
            return;
        }

        $this->loadFromOrganisation($org->fresh());
    }

    public function toggleFeaturedTribe(int $tribeId): void
    {
        if (in_array($tribeId, $this->featured_tribe_ids, true)) {
            $this->featured_tribe_ids = array_values(array_filter($this->featured_tribe_ids, fn ($id) => (int) $id !== $tribeId));
            return;
        }

        $this->featured_tribe_ids[] = $tribeId;
        $this->featured_tribe_ids = array_values(array_unique(array_map('intval', $this->featured_tribe_ids)));
    }

    public function render()
    {
        $tribes = Tribe::query()->orderBy('name')->get(['id', 'name', 'hero_emoji']);
        $featuredTribes = $tribes->whereIn('id', $this->featured_tribe_ids)->values();

        return view('livewire.cms.site', [
            'tribes' => $tribes,
            'featuredTribes' => $featuredTribes,
        ])
            ->layout('layouts.cms');
    }
}
