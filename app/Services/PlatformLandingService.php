<?php

namespace App\Services;

use App\Models\Comic;
use App\Models\PlatformLandingSetting;
use App\Models\Tribe;
use Illuminate\Support\Facades\Storage;

class PlatformLandingService
{
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
            'font_heading' => 'Baloo 2',
            'font_body' => 'Inter',
            'peoples_section_title' => null,
            'peoples_count' => 65,
            'featured_tribe_ids' => [],
            'cta_title' => 'Ready to start your child\'s cultural journey?',
            'cta_subtitle' => 'Join thousands of children learning about their heritage through stories and songs.',
            'seo_title' => null,
            'seo_description' => null,
        ];
    }

    public function draft(): array
    {
        $row = PlatformLandingSetting::instance();
        $draft = is_array($row->draft) ? $row->draft : [];

        return array_merge($this->defaults(), $draft);
    }

    public function published(): array
    {
        $row = PlatformLandingSetting::instance();
        $published = is_array($row->published) && $row->published !== []
            ? $row->published
            : null;

        return array_merge($this->defaults(), $published ?? $this->draft());
    }

    public function saveDraft(array $data): void
    {
        $row = PlatformLandingSetting::instance();
        $row->update(['draft' => array_merge($this->draft(), $data)]);
    }

    public function publish(int $userId): void
    {
        $row = PlatformLandingSetting::instance();
        $row->update([
            'published' => $this->draft(),
            'published_at' => now(),
            'published_by' => $userId,
        ]);
    }

    public function discardDraft(): void
    {
        $row = PlatformLandingSetting::instance();
        $published = is_array($row->published) ? $row->published : [];

        $row->update(['draft' => $published !== [] ? $published : $this->defaults()]);
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

        return [
            'landing' => $settings,
            'heroComic' => $heroComic,
            'heroImageUrl' => $heroImageUrl,
            'featuredPeoples' => $featuredPeoples,
            'peoplesCount' => $peoplesCount,
            'peoplesSectionTitle' => $settings['peoples_section_title']
                ?: heritage('explore_peoples_count', ['count' => $peoplesCount]),
            'seoTitle' => $settings['seo_title'] ?: config('app.name'),
            'seoDescription' => $settings['seo_description']
                ?: 'Interactive cultural comics, songs, and language learning for Ugandan children.',
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
}
