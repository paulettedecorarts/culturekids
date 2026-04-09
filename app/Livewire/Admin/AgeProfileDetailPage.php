<?php

namespace App\Livewire\Admin;

use App\Models\AgeProfile;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class AgeProfileDetailPage extends Component
{
    public ?AgeProfile $profile = null;

    public bool $isCreate = false;

    public bool $isEditing = false;

    public string $name = '';

    public string $key = '';

    public int $min_age = 2;

    public ?int $max_age = 3;

    public string $icon_emoji = '';

    public string $color = '#C44B2B';

    public string $ui_scale = 'standard';

    public int $touch_target_px = 52;

    public string $reading_level = 'short_labels';

    public string $activity_complexity = 'guided';

    public bool $is_audio_first = false;

    public bool $is_active = true;

    public int $sort_order = 100;

    public string $modules_csv = '';

    public string $ui_features_text = '';

    public string $notes = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->profile = AgeProfile::findOrFail($id);
            $this->fillFromProfile($this->profile);
            $this->isCreate = false;
            $this->isEditing = false;

            return;
        }

        $this->isCreate = true;
        $this->isEditing = true;
    }

    public function startEditing(): void
    {
        $this->isEditing = true;
    }

    public function cancelEditing(): void
    {
        if ($this->profile) {
            $this->profile->refresh();
            $this->fillFromProfile($this->profile);
            $this->isEditing = false;

            return;
        }

        $this->redirectRoute('admin.age-categories', navigate: true);
    }

    public function saveProfile()
    {
        $data = $this->validate($this->rules());
        $this->validateAgeWindow($data['min_age'], $data['max_age']);

        $payload = [
            'name' => $data['name'],
            'key' => $data['key'],
            'min_age' => $data['min_age'],
            'max_age' => $data['max_age'],
            'icon_emoji' => $data['icon_emoji'] ?: null,
            'color' => $data['color'] ?: null,
            'ui_scale' => $data['ui_scale'],
            'touch_target_px' => $data['touch_target_px'],
            'reading_level' => $data['reading_level'],
            'activity_complexity' => $data['activity_complexity'],
            'is_audio_first' => $data['is_audio_first'],
            'is_active' => $data['is_active'],
            'sort_order' => $data['sort_order'],
            'content_access_rules' => [
                'modules' => $this->parseCsv($this->modules_csv),
                'notes' => trim($this->notes),
            ],
            'ui_features' => $this->parseLines($this->ui_features_text),
        ];

        if ($this->profile) {
            $this->profile->update($payload);
            session()->flash('message', 'Age profile updated.');
        } else {
            $this->profile = AgeProfile::create($payload);
            session()->flash('message', 'Age profile created.');
        }

        return $this->redirectRoute('admin.age-categories.detail', ['id' => $this->profile->id], navigate: true);
    }

    public function deleteProfile(): void
    {
        if (! $this->profile) {
            return;
        }

        $assignedCount = $this->profile->childProfiles()->count();
        if ($assignedCount > 0) {
            $this->addError('delete', 'Cannot delete this age profile because children are assigned to it.');

            return;
        }

        $this->profile->delete();
        session()->flash('message', 'Age profile deleted.');
        $this->redirectRoute('admin.age-categories', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.age-profile-detail-page', [
            'emojiPack' => $this->emojiPack(),
        ]);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9_]+$/', Rule::unique('age_profiles', 'key')->ignore($this->profile?->id)],
            'min_age' => ['required', 'integer', 'min:0', 'max:17'],
            'max_age' => ['nullable', 'integer', 'min:0', 'max:18', 'gte:min_age'],
            'icon_emoji' => ['nullable', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:20'],
            'ui_scale' => ['required', Rule::in(['giant', 'large', 'standard', 'compact'])],
            'touch_target_px' => ['required', 'integer', 'min:36', 'max:120'],
            'reading_level' => ['required', Rule::in(['audio_only', 'short_labels', 'short_words', 'sentences'])],
            'activity_complexity' => ['required', Rule::in(['single_action', 'two_choice', 'guided', 'multi_choice', 'open_ended'])],
            'is_audio_first' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }

    private function fillFromProfile(AgeProfile $profile): void
    {
        $this->name = $profile->name;
        $this->key = $profile->key;
        $this->min_age = $profile->min_age;
        $this->max_age = $profile->max_age;
        $this->icon_emoji = (string) ($profile->icon_emoji ?? '');
        $this->color = (string) ($profile->color ?? '#C44B2B');
        $this->ui_scale = $profile->ui_scale;
        $this->touch_target_px = $profile->touch_target_px;
        $this->reading_level = $profile->reading_level;
        $this->activity_complexity = $profile->activity_complexity;
        $this->is_audio_first = $profile->is_audio_first;
        $this->is_active = $profile->is_active;
        $this->sort_order = $profile->sort_order;
        $this->modules_csv = implode(', ', data_get($profile->content_access_rules, 'modules', []));
        $this->ui_features_text = implode(PHP_EOL, $profile->ui_features ?? []);
        $this->notes = (string) data_get($profile->content_access_rules, 'notes', '');
    }

    private function validateAgeWindow(int $minAge, ?int $maxAge): void
    {
        $query = AgeProfile::query()
            ->when($this->profile, fn ($q) => $q->where('id', '!=', $this->profile->id))
            ->where(function ($q) use ($minAge, $maxAge): void {
                $upper = $maxAge ?? 200;
                $q->where('min_age', '<', $upper)
                    ->where(function ($inner) use ($minAge): void {
                        $inner->whereNull('max_age')->orWhere('max_age', '>', $minAge);
                    });
            });

        if ($query->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'min_age' => 'This age range overlaps with another profile.',
                'max_age' => 'Choose a non-overlapping range.',
            ]);
        }
    }

    private function parseCsv(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function parseLines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function emojiPack(): array
    {
        return [
            '🌱', '🌿', '🌳', '🌟', '👶', '🧒', '👧', '🧑',
            '🎵', '📚', '🧩', '🎨', '🧠', '🗣️', '🔤', '🎯',
            '🦁', '🐘', '🦒', '🦓', '🐒', '🐦', '🌈', '☀️',
            '⭐', '🔥', '💡', '🚀', '🎉', '❤️', '🤝', '🏆',
        ];
    }

}
