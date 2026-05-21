<?php

namespace App\Livewire\CMS\Drawings;

use App\Livewire\Concerns\CoercesNumericFormFields;
use App\Livewire\Concerns\LogsFileUploads;
use App\Livewire\Concerns\UsesPortalContext;
use App\Livewire\Concerns\ValidatesOnlyChangedOnEdit;
use App\Models\Drawing;
use App\Models\Tribe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class DrawingEditor extends Component
{
    use CoercesNumericFormFields;
    use LogsFileUploads, UsesPortalContext, ValidatesOnlyChangedOnEdit, WithFileUploads;

    public ?Drawing $drawing = null;

    public bool $isEdit = false;

    // Form fields
    public $tribe_id = '';

    public $title = '';

    public $description = '';

    public $drawing_type = 'coloring';

    public $difficulty_level = 'easy';

    public $age_min = 3;

    public $age_max = 12;

    public $star_points = 10;

    public $status = 'draft';

    // File uploads
    public $template_file = null;

    public $preview_file = null;

    // Configuration
    public $materials = [];

    public $color_palette = [];

    public $tools_config = [];

    // UI state
    public $materialInput = '';

    public $colorInput = '#FF0000';

    // Type-specific metadata fields (flat — Livewire doesn't support nested dot-notation wire:model)
    public string $meta_scene_description = '';

    public string $meta_colour_hint = '';

    public string $meta_hero_name = '';

    public string $meta_hero_title = '';

    public string $meta_hero_instructions = '';

    public string $meta_design_prompt = '';

    public string $meta_design_stamps = '';

    public string $meta_free_draw_prompt = '';

    public string $meta_free_draw_checklist = '';

    // Type-specific metadata
    public array $metadata = [];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->drawing = Drawing::findOrFail($id);
            $this->isEdit = true;
            $this->loadDrawingData();
        } else {
            $this->initializeDefaults();
        }
    }

    protected function loadDrawingData(): void
    {
        $this->tribe_id = $this->drawing->tribe_id;
        $this->title = $this->drawing->title;
        $this->description = $this->drawing->description;
        $this->drawing_type = $this->drawing->drawing_type;
        $this->difficulty_level = $this->drawing->difficulty_level;
        $this->age_min = $this->drawing->age_min;
        $this->age_max = $this->drawing->age_max;
        $this->star_points = $this->drawing->star_points;
        $this->status = $this->drawing->status;
        $this->materials = $this->drawing->materials ?? [];
        $this->color_palette = $this->drawing->color_palette ?? $this->drawing->getDefaultColorPaletteAttribute();
        $this->tools_config = $this->drawing->tools_config ?? $this->drawing->getDefaultToolsConfigAttribute();

        // Load type-specific metadata
        $meta = $this->drawing->metadata ?? [];
        $this->meta_scene_description = $meta['coloring']['scene_description'] ?? '';
        $this->meta_colour_hint = $meta['coloring']['colour_hint'] ?? '';
        $this->meta_hero_name = $meta['hero']['name'] ?? '';
        $this->meta_hero_title = $meta['hero']['title'] ?? '';
        $this->meta_hero_instructions = $meta['hero']['instructions'] ?? '';
        $this->meta_design_prompt = $meta['design']['prompt'] ?? '';
        $this->meta_design_stamps = $meta['design']['stamps'] ?? '';
        $this->meta_free_draw_prompt = $meta['free_draw']['prompt'] ?? '';
        $this->meta_free_draw_checklist = $meta['free_draw']['checklist'] ?? '';
        $this->metadata = $this->drawing->metadata ?? [];
    }

    protected function initializeDefaults(): void
    {
        $this->tribe_id = Tribe::first()?->id ?? '';
        $this->materials = ['Crayons', 'Paper'];
        $this->color_palette = [
            '#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF',
            '#FFA500', '#800080', '#FFC0CB', '#A52A2A', '#808080', '#000000',
        ];
        $this->tools_config = [
            'brushes' => [
                ['name' => 'Small Brush', 'size' => 2],
                ['name' => 'Medium Brush', 'size' => 5],
                ['name' => 'Large Brush', 'size' => 10],
            ],
            'tools' => ['brush', 'eraser', 'fill'],
            'features' => ['undo', 'redo', 'clear'],
        ];
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::orderBy('name')->get();
    }

    public function addMaterial(): void
    {
        if (trim($this->materialInput) !== '' && ! in_array(trim($this->materialInput), $this->materials)) {
            $this->materials[] = trim($this->materialInput);
            $this->materialInput = '';
        }
    }

    public function removeMaterial(int $index): void
    {
        unset($this->materials[$index]);
        $this->materials = array_values($this->materials);
    }

    public function addColor(): void
    {
        if (! in_array($this->colorInput, $this->color_palette)) {
            $this->color_palette[] = $this->colorInput;
        }
    }

    public function removeColor(int $index): void
    {
        unset($this->color_palette[$index]);
        $this->color_palette = array_values($this->color_palette);
    }

    public function resetToDefaultColors(): void
    {
        $this->color_palette = [
            '#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF',
            '#FFA500', '#800080', '#FFC0CB', '#A52A2A', '#808080', '#000000',
            '#FFFFFF', '#8B4513', '#90EE90', '#FFB6C1', '#20B2AA', '#DDA0DD',
        ];
    }

    protected function rules(): array
    {
        return [
            'tribe_id' => ['required', 'exists:tribes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'drawing_type' => ['required', 'in:coloring,colour_by_number,hero_drawing,design_tool,free_draw'],
            'difficulty_level' => ['required', 'in:easy,medium,hard'],
            'age_min' => ['required', 'integer', 'min:1', 'max:18'],
            'age_max' => ['required', 'integer', 'min:1', 'max:18', 'gte:age_min'],
            'star_points' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'in:draft,published,archived'],
            'template_file' => ['nullable', 'image', 'max:10240'], // 10MB max
            'preview_file' => ['nullable', 'image', 'max:5120'], // 5MB max
        ];
    }

    public function save(): void
    {
        $this->validate();

        Log::info('DrawingEditor Save Attempt', [
            'is_edit' => $this->isEdit,
            'drawing_id' => $this->drawing?->id,
            'title' => $this->title,
            'drawing_type' => $this->drawing_type,
            'has_template_file' => $this->template_file !== null,
            'has_preview_file' => $this->preview_file !== null,
        ]);

        DB::transaction(function (): void {
            $drawing = $this->drawing ?? new Drawing;

            $drawing->fill([
                'tribe_id' => $this->tribe_id,
                'title' => $this->title,
                'description' => $this->description,
                'drawing_type' => $this->drawing_type,
                'difficulty_level' => $this->difficulty_level,
                'age_min' => $this->age_min,
                'age_max' => $this->age_max,
                'star_points' => $this->star_points,
                'status' => $this->status,
                'materials' => $this->materials,
                'color_palette' => $this->color_palette,
                'tools_config' => $this->tools_config,
                'metadata' => $this->metadata ?: null,
            ]);

            $drawing->save();

            // Handle file uploads
            if ($this->template_file) {
                $templatePath = $this->template_file->storeAs(
                    'drawings/templates',
                    'template_'.$drawing->id.'_'.time().'.'.$this->template_file->getClientOriginalExtension(),
                    'public'
                );
                $drawing->template_path = $templatePath;
                Log::info('Template file uploaded', ['path' => $templatePath]);
            }

            if ($this->preview_file) {
                $previewPath = $this->preview_file->storeAs(
                    'drawings/previews',
                    'preview_'.$drawing->id.'_'.time().'.'.$this->preview_file->getClientOriginalExtension(),
                    'public'
                );
                $drawing->preview_path = $previewPath;
                Log::info('Preview file uploaded', ['path' => $previewPath]);
            }

            if ($this->template_file || $this->preview_file) {
                $drawing->save();
            }

            $this->drawing = $drawing;
        });

        session()->flash('message', $this->isEdit ? 'Drawing activity updated successfully!' : 'Drawing activity created successfully!');

        $this->redirectRoute($this->portalRouteName('drawings.show'), ['id' => $this->drawing->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.drawings.drawing-editor', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
