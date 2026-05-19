<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ScopesContentTranslations;
use App\Livewire\Concerns\UsesPortalContext;
use App\Models\ContentTranslation;
use App\Models\OrganisationContentDecision;
use App\Services\ContentTranslationFormPresenter;
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

    public ?int $x_position = null;

    public ?int $y_position = null;

    public ?int $width = null;

    public ?int $height = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->tag = $this->contentTranslationQuery()->findOrFail($id);
            $this->content_type = $this->tag->content_type;
            $this->content_id = $this->tag->content_id;
            $this->sub_item_key = $this->tag->sub_item_key;
            $this->panel_id = $this->tag->panel_id;
            $this->hydrateFieldsFromTag($this->tag);
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
        } elseif ($this->content_id) {
            $this->bootstrapFirstSubItem();
        }
        $this->syncPanelIdFromSubItem();
        if ($this->shouldOfferSourcePrefill()) {
            $this->applyNativeValues(fillOnlyEmpty: true);
        }
    }

    public function updatedContentType(): void
    {
        $this->content_id = null;
        $this->sub_item_key = null;
        $this->panel_id = null;
        $this->resetFieldValues();
    }

    public function updatedContentId(): void
    {
        $this->sub_item_key = null;
        $this->panel_id = null;
        $this->bootstrapFirstSubItem();
        $this->syncPanelIdFromSubItem();
        if ($this->shouldOfferSourcePrefill()) {
            $this->applyNativeValues(fillOnlyEmpty: true);
        }
    }

    public function updatedSubItemKey(): void
    {
        $this->syncPanelIdFromSubItem();
        if ($this->shouldOfferSourcePrefill()) {
            $this->applyNativeValues(fillOnlyEmpty: true);
        }
    }

    public function selectSubItem(string $key): void
    {
        $this->sub_item_key = $key;
        $this->syncPanelIdFromSubItem();
        if ($this->shouldOfferSourcePrefill()) {
            $this->applyNativeValues(fillOnlyEmpty: true);
        }
    }

    public function syncFromSource(): void
    {
        $this->applyNativeValues(fillOnlyEmpty: false);
        session()->flash('message', 'Fields loaded from source content.');
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
            $rules['x_position'] = ['nullable', 'integer', 'min:0', 'max:100'];
            $rules['y_position'] = ['nullable', 'integer', 'min:0', 'max:100'];
            $rules['width'] = ['nullable', 'integer', 'min:1', 'max:500'];
            $rules['height'] = ['nullable', 'integer', 'min:1', 'max:500'];
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
            'x_position' => $data['content_type'] === OrganisationContentDecision::TYPE_STORY ? ($data['x_position'] ?? null) : null,
            'y_position' => $data['content_type'] === OrganisationContentDecision::TYPE_STORY ? ($data['y_position'] ?? null) : null,
            'width' => $data['content_type'] === OrganisationContentDecision::TYPE_STORY ? ($data['width'] ?? null) : null,
            'height' => $data['content_type'] === OrganisationContentDecision::TYPE_STORY ? ($data['height'] ?? null) : null,
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
        $catalog = $this->catalog();
        $presenter = $this->formPresenter();

        $subtype = $this->content_id
            ? $presenter->resolveSubtype($this->content_type, (int) $this->content_id)
            : null;

        $fieldSchema = $presenter->fieldSchema($this->content_type, $subtype, $this->sub_item_key);

        $sourcePreview = ($this->content_id && $this->canShowWorkspace())
            ? $presenter->sourcePreview($this->content_type, (int) $this->content_id, $this->sub_item_key)
            : null;

        $subItemNav = ($this->content_id && $this->subItemsRequired())
            ? $presenter->subItemNav($this->content_type, (int) $this->content_id, $this->sub_item_key)
            : [];

        return view('livewire.admin.translation-form-page', [
            'typeOptions' => $catalog->typeOptions(),
            'contentOptions' => $this->content_type
                ? $catalog->contentItemsForType($this->content_type, $this->organisationId(), $this->isSuperAdminUser())
                : collect(),
            'subItemOptions' => $this->content_id
                ? $catalog->subItemOptions($this->content_type, (int) $this->content_id)
                : [],
            'subItemsRequired' => $this->subItemsRequired(),
            'contextLabel' => $this->tag ? $catalog->contextLabel($this->tag) : null,
            'listRoute' => $this->portalRouteName('translations'),
            'fieldSchema' => $fieldSchema,
            'sourcePreview' => $sourcePreview,
            'subItemNav' => $subItemNav,
            'showWorkspace' => $this->canShowWorkspace(),
        ])->layout($this->portalLayout());
    }

    protected function formPresenter(): ContentTranslationFormPresenter
    {
        return app(ContentTranslationFormPresenter::class);
    }

    protected function canShowWorkspace(): bool
    {
        if (! $this->content_id) {
            return false;
        }

        if ($this->subItemsRequired()) {
            return filled($this->sub_item_key);
        }

        return true;
    }

    protected function shouldOfferSourcePrefill(): bool
    {
        if (! $this->content_id) {
            return false;
        }

        if ($this->subItemsRequired() && ! filled($this->sub_item_key)) {
            return false;
        }

        return $this->isCreate || $this->tag;
    }

    protected function bootstrapFirstSubItem(): void
    {
        if (! $this->content_id || ! $this->subItemsRequired() || filled($this->sub_item_key)) {
            return;
        }

        $opts = $this->catalog()->subItemOptions($this->content_type, (int) $this->content_id);
        if ($opts === []) {
            return;
        }

        $this->sub_item_key = $opts[0]['key'];
    }

    protected function syncPanelIdFromSubItem(): void
    {
        if ($this->content_type === OrganisationContentDecision::TYPE_STORY
            && $this->sub_item_key
            && str_starts_with($this->sub_item_key, 'panel:')) {
            $this->panel_id = (int) substr($this->sub_item_key, 6);

            return;
        }

        if ($this->content_type !== OrganisationContentDecision::TYPE_STORY) {
            $this->panel_id = null;
        }
    }

    protected function hydrateFieldsFromTag(ContentTranslation $tag): void
    {
        $this->word = $tag->word;
        $this->translation = $tag->translation;
        $this->phonetic = $tag->phonetic;
        $this->x_position = $tag->x_position;
        $this->y_position = $tag->y_position;
        $this->width = $tag->width;
        $this->height = $tag->height;
    }

    protected function resetFieldValues(): void
    {
        $this->word = '';
        $this->translation = null;
        $this->phonetic = null;
        $this->x_position = null;
        $this->y_position = null;
        $this->width = null;
        $this->height = null;
    }

    protected function applyNativeValues(bool $fillOnlyEmpty): void
    {
        if (! $this->content_id) {
            return;
        }

        $values = $this->formPresenter()->valuesFromNative(
            $this->content_type,
            (int) $this->content_id,
            $this->sub_item_key,
        );

        $map = [
            'word' => 'word',
            'translation' => 'translation',
            'phonetic' => 'phonetic',
            'x_position' => 'x_position',
            'y_position' => 'y_position',
            'width' => 'width',
            'height' => 'height',
        ];

        foreach ($map as $prop => $key) {
            $incoming = $values[$key] ?? null;
            if ($incoming === null || $incoming === '') {
                continue;
            }
            if ($fillOnlyEmpty && filled($this->{$prop})) {
                continue;
            }
            $this->{$prop} = $incoming;
        }
    }
}
