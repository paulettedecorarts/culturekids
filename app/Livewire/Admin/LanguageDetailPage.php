<?php

namespace App\Livewire\Admin;

use App\Models\Language;
use App\Services\TranslationCoverageService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class LanguageDetailPage extends Component
{
    public ?Language $language = null;

    public bool $isCreate = false;

    public bool $isEditing = false;

    public string $name = '';

    public ?string $native_name = null;

    public string $code = '';

    public ?string $flag_emoji = null;

    public int $translation_coverage = 0;

    public bool $audio_pack_available = false;

    public string $status = 'pending';

    public bool $is_active = true;

    public int $sort_order = 100;

    public ?string $notes = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->language = Language::findOrFail($id);
            app(TranslationCoverageService::class)->syncLanguageRegistryWithStatus($this->language->code);
            $this->language->refresh();
            $this->fillFromLanguage($this->language);
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
        if ($this->language) {
            $this->language->refresh();
            $this->fillFromLanguage($this->language);
            $this->isEditing = false;

            return;
        }

        $this->redirectRoute('admin.languages', navigate: true);
    }

    public function saveLanguage()
    {
        $data = $this->validate($this->rules());

        $coverageService = app(TranslationCoverageService::class);
        $coverage = $coverageService->coveragePercentForLanguageCode($data['code']);

        $payload = [
            'name' => $data['name'],
            'native_name' => $data['native_name'],
            'code' => $data['code'],
            'flag_emoji' => $data['flag_emoji'],
            'translation_coverage' => $coverage,
            'audio_pack_available' => $data['audio_pack_available'],
            'status' => $coverageService->derivedStatus($coverage),
            'is_active' => $data['is_active'],
            'sort_order' => $data['sort_order'],
            'notes' => $data['notes'],
        ];

        if ($this->language) {
            $this->language->update($payload);
            session()->flash('message', 'Language updated.');
        } else {
            $this->language = Language::create($payload);
            session()->flash('message', 'Language created.');
        }

        $coverageService->syncLanguageRegistryWithStatus($this->language->code);
        $this->language->refresh();

        return $this->redirectRoute('admin.languages.detail', ['id' => $this->language->id], navigate: true);
    }

    public function deleteLanguage()
    {
        if (! $this->language) {
            return null;
        }

        $this->language->delete();
        session()->flash('message', 'Language deleted.');

        return $this->redirectRoute('admin.languages', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.language-detail-page');
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'native_name' => ['nullable', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:35', Rule::unique('languages', 'code')->ignore($this->language?->id)],
            'flag_emoji' => ['nullable', 'string', 'max:10'],
            'audio_pack_available' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function fillFromLanguage(Language $language): void
    {
        $this->name = $language->name;
        $this->native_name = $language->native_name;
        $this->code = $language->code;
        $this->flag_emoji = $language->flag_emoji;
        $this->translation_coverage = $language->translation_coverage;
        $this->audio_pack_available = $language->audio_pack_available;
        $this->status = $language->status;
        $this->is_active = $language->is_active;
        $this->sort_order = $language->sort_order;
        $this->notes = $language->notes;
    }
}
