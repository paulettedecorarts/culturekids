<?php

namespace App\Livewire\Concerns;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * On edit screens, do not run file validation rules unless the user selected a new upload.
 */
trait ValidatesOnlyChangedOnEdit
{
    public function validate($rules = null, $messages = [], $attributes = [])
    {
        if (method_exists($this, 'normalizeNumericFormFields')) {
            $this->normalizeNumericFormFields();
        }

        if ($rules === null) {
            $rules = $this->validationRules();
        }

        if ($this->isFormEditMode() && is_array($rules)) {
            $rules = $this->omitUploadRulesWithoutNewFiles($rules);
        }

        return parent::validate($rules, $messages, $attributes);
    }

    protected function validationRules(): array
    {
        if (method_exists($this, 'rules')) {
            $rules = $this->rules();

            return is_array($rules) ? $rules : [];
        }

        if (property_exists($this, 'rules') && is_array($this->rules)) {
            return $this->rules;
        }

        return [];
    }

    protected function isFormEditMode(): bool
    {
        if (property_exists($this, 'editing') && $this->editing) {
            return true;
        }

        if (property_exists($this, 'isEdit') && $this->isEdit) {
            return true;
        }

        if (property_exists($this, 'comicId') && ! empty($this->comicId)) {
            return true;
        }

        foreach ([
            'clan', 'song', 'game', 'maze', 'activity', 'drawing',
            'puzzle', 'spotDifference', 'wordSearch', 'cultureActivity', 'languageActivity',
        ] as $modelProperty) {
            if (! property_exists($this, $modelProperty)) {
                continue;
            }

            $model = $this->{$modelProperty};

            if ($model !== null && (is_object($model) ? ($model->exists ?? true) : true)) {
                return true;
            }
        }

        return false;
    }

    protected function omitUploadRulesWithoutNewFiles(array $rules): array
    {
        foreach (array_keys($rules) as $key) {
            if (! $this->isUploadValidationKey($key)) {
                continue;
            }

            $base = str_replace('.*', '', $key);

            if (! $this->hasPendingUpload($base)) {
                unset($rules[$key]);
            }
        }

        return $rules;
    }

    protected function isUploadValidationKey(string $key): bool
    {
        $base = str_replace('.*', '', $key);

        if (preg_match('/(_file|_image|_upload|Files?|files|logo|audio|video|media|cover_image|panel_files|puzzle_image|replacement_image)/i', $base)) {
            return true;
        }

        return in_array($base, [
            'cover_image',
            'panel_files',
            'puzzle_image',
            'replacement_image',
            'audio_file',
            'video_file',
            'media_file',
            'logo',
            'logo_upload',
            'template_file',
            'preview_file',
            'flashcardSlideImageUploads',
        ], true);
    }

    protected function hasPendingUpload(string $property): bool
    {
        if (! property_exists($this, $property)) {
            return false;
        }

        $value = $this->{$property};

        if ($value instanceof TemporaryUploadedFile) {
            return true;
        }

        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $item) {
            if ($item instanceof TemporaryUploadedFile) {
                return true;
            }

            if (is_array($item)) {
                foreach ($item as $nested) {
                    if ($nested instanceof TemporaryUploadedFile) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
