<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ScopesContentTranslations;
use App\Livewire\Concerns\UsesPortalContext;
use App\Models\ContentTranslation;
use App\Models\OrganisationContentDecision;
use App\Services\ContentTranslationPersistenceService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TranslationFormPage extends Component
{
    use ScopesContentTranslations;
    use UsesPortalContext;

    public ?ContentTranslation $tag = null;

    public bool $isCreate = false;

    public string $content_type = OrganisationContentDecision::TYPE_STORY;

    public ?int $content_id = null;

    public ?string $sub_item_key = null;

    public ?int $panel_id = null;

    public string $word = '';

    public ?string $translation = null;

    public ?string $phonetic = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->tag = $this->contentTranslationQuery()->findOrFail($id);
            $this->content_type = $this->tag->content_type;
            $this->content_id = $this->tag->content_id;
            $this->sub_item_key = $this->tag->sub_item_key;
            $this->panel_id = $this->tag->panel_id;
            $this->word = $this->tag->word;
            $this->translation = $this->tag->translation;
            $this->phonetic = $this->tag->phonetic;
            $this->isCreate = false;

            return;
        }

        $this->isCreate = true;
        $this->content_type = request()->string('content_type')->toString() ?: OrganisationContentDecision::TYPE_STORY;
        $prefillContent = request()->integer('content_id');
        if ($prefillContent > 0) {
            $this->content_id = $prefillContent;
        }
        $prefillSub = request()->string('sub_item_key')->toString();
        if ($prefillSub !== '') {
            $this->sub_item_key = $prefillSub;
        }
        if ($this->content_type === OrganisationContentDecision::TYPE_STORY && $this->sub_item_key && str_starts_with($this->sub_item_key, 'panel:')) {
            $this->panel_id = (int) substr($this->sub_item_key, 6);
        }
    }

    public function updatedContentType(): void
    {
        $this->content_id = null;
        $this->sub_item_key = null;
        $this->panel_id = null;
    }

    public function updatedContentId(): void
    {
        $this->sub_item_key = null;
        $this->panel_id = null;
    }

    public function updatedSubItemKey(): void
    {
        if ($this->content_type === OrganisationContentDecision::TYPE_STORY && $this->sub_item_key && str_starts_with($this->sub_item_key, 'panel:')) {
            $this->panel_id = (int) substr($this->sub_item_key, 6);
        }
    }

    protected function rules(): array
    {
        $rules = [
            'content_type' => ['required', Rule::in(array_keys(config('content_translations.types', [])))],
            'content_id' => ['required', 'integer', 'min:1'],
            'word' => ['required', 'string', 'max:255'],
            'translation' => ['nullable', 'string', 'max:255'],
            'phonetic' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->subItemsRequired()) {
            $rules['sub_item_key'] = ['required', 'string', 'max:80'];
        } else {
            $rules['sub_item_key'] = ['nullable', 'string', 'max:80'];
        }

        if ($this->content_type === OrganisationContentDecision::TYPE_STORY) {
            $rules['panel_id'] = [
                'required',
                Rule::exists('comic_panels', 'id')->where(fn ($q) => $q->where('comic_id', $this->content_id)),
            ];
        }

        return $rules;
    }

    protected function subItemsRequired(): bool
    {
        $sub = data_get(config('content_translations.types.'.$this->content_type), 'sub_items');

        return $sub !== null && $sub !== '';
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($data['content_type'] === OrganisationContentDecision::TYPE_STORY) {
            $data['sub_item_key'] = 'panel:'.$data['panel_id'];
        }

        $payload = [
            'content_type' => $data['content_type'],
            'content_id' => $data['content_id'],
            'panel_id' => $data['content_type'] === OrganisationContentDecision::TYPE_STORY ? $data['panel_id'] : null,
            'sub_item_key' => $data['sub_item_key'] ?? null,
            'word' => trim($data['word']),
            'translation' => isset($data['translation']) ? trim((string) $data['translation']) : null,
            'phonetic' => isset($data['phonetic']) ? trim((string) $data['phonetic']) : null,
        ];

        if ($payload['translation'] === '') {
            $payload['translation'] = null;
        }
        if ($payload['phonetic'] === '') {
            $payload['phonetic'] = null;
        }

        $persistence = app(ContentTranslationPersistenceService::class);

        if ($this->tag) {
            $this->tag->update($payload);
            $persistence->applyNativeSync($this->tag->fresh());
            session()->flash('message', 'Translation updated.');
        } else {
            $tag = ContentTranslation::create($payload);
            $persistence->applyNativeSync($tag);
            session()->flash('message', 'Translation created.');
        }

        $this->redirectRoute($this->portalRouteName('translations'), navigate: true);
    }

    public function delete(): void
    {
        if (! $this->tag) {
            return;
        }

        $this->tag->delete();
        session()->flash('message', 'Translation deleted.');

        $this->redirectRoute($this->portalRouteName('translations'), navigate: true);
    }

    public function render()
    {
        $subItems = $this->content_id
            ? $this->catalog()->subItemOptions($this->content_type, $this->content_id)
            : [];

        $catalog = $this->catalog();

        return view('livewire.admin.translation-form-page', [
            'typeOptions' => $catalog->typeOptions(),
            'contentOptions' => $this->content_type
                ? $catalog->contentItemsForType($this->content_type, $this->organisationId(), $this->isSuperAdminUser())
                : collect(),
            'subItemOptions' => $subItems,
            'subItemsRequired' => $this->subItemsRequired(),
            'contextLabel' => $this->tag ? $catalog->contextLabel($this->tag) : null,
            'listRoute' => $this->portalRouteName('translations'),
        ])->layout($this->portalLayout());
    }
}
