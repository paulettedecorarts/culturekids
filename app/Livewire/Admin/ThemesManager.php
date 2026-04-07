<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Theme;
use App\Models\AuditLog;
use Illuminate\Support\Str;

#[Layout('layouts.admin')]
class ThemesManager extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editing = false;
    public $showPreview = false;
    
    // Form fields
    public $selectedId;
    public $name;
    public $description;
    public $slug;
    
    // Colors
    public $primary = '#C44B2B';
    public $secondary = '#E8872A';
    public $accent = '#D4A017';
    public $success = '#4A7C59';
    public $warning = '#F2A84E';
    public $danger = '#9A3218';
    public $background = '#FAF6F0';
    public $surface = '#FFFFFF';
    public $text_primary = '#1A1208';
    public $text_secondary = '#6B5544';
    public $text_muted = '#9C8875';

    protected $rules = [
        'name' => 'required|min:3|max:100',
        'slug' => 'required|alpha_dash|unique:themes,slug',
        'description' => 'nullable|max:500',
    ];

    public function mount()
    {
        // Load default colors
        $defaults = Theme::defaultColors();
        foreach ($defaults as $key => $value) {
            $this->$key = $value;
        }
    }

    public function updatedName($value)
    {
        if (!$this->editing) {
            $this->slug = Str::slug($value, '_');
        }
    }

    public function create()
    {
        $this->resetForm();
        $this->editing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $theme = Theme::findOrFail($id);
        $this->selectedId = $id;
        $this->name = $theme->name;
        $this->slug = $theme->slug;
        $this->description = $theme->description;
        
        // Load colors
        foreach ($theme->colors as $key => $value) {
            $this->$key = $value;
        }
        
        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editing) {
            $rules['slug'] = 'required|alpha_dash|unique:themes,slug,' . $this->selectedId;
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'colors' => [
                'primary' => $this->primary,
                'secondary' => $this->secondary,
                'accent' => $this->accent,
                'success' => $this->success,
                'warning' => $this->warning,
                'danger' => $this->danger,
                'background' => $this->background,
                'surface' => $this->surface,
                'text_primary' => $this->text_primary,
                'text_secondary' => $this->text_secondary,
                'text_muted' => $this->text_muted,
            ],
        ];

        if ($this->editing) {
            $theme = Theme::findOrFail($this->selectedId);
            $theme->update($data);
            
            AuditLog::record('UPDATE', "themes/{$theme->id}", [
                'theme_name' => $this->name,
            ]);
            
            session()->flash('message', 'Theme updated successfully.');
        } else {
            $theme = Theme::create($data);
            
            AuditLog::record('CREATE', "themes/{$theme->id}", [
                'theme_name' => $this->name,
            ]);
            
            session()->flash('message', 'Theme created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        $theme = Theme::findOrFail($id);
        
        if ($theme->is_default) {
            session()->flash('error', 'Cannot delete the default theme.');
            return;
        }
        
        AuditLog::record('DELETE', "themes/{$theme->id}", [
            'theme_name' => $theme->name,
        ]);
        
        $theme->delete();
        session()->flash('message', 'Theme removed successfully.');
    }

    public function setDefault($id)
    {
        // Remove default from all themes
        Theme::where('is_default', true)->update(['is_default' => false]);
        
        // Set new default
        $theme = Theme::findOrFail($id);
        $theme->is_default = true;
        $theme->save();
        
        AuditLog::record('UPDATE', "themes/{$theme->id}", [
            'action' => 'set_default',
            'theme_name' => $theme->name,
        ]);
        
        session()->flash('message', "'{$theme->name}' set as default theme.");
    }

    public function applyPreset($preset)
    {
        $presets = $this->getPresets();
        if (isset($presets[$preset])) {
            foreach ($presets[$preset]['colors'] as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    private function getPresets()
    {
        return [
            'savanna' => [
                'name' => 'Savanna Sunset',
                'colors' => [
                    'primary' => '#C44B2B',
                    'secondary' => '#E8872A',
                    'accent' => '#D4A017',
                    'success' => '#4A7C59',
                    'warning' => '#F2A84E',
                    'danger' => '#9A3218',
                    'background' => '#FAF6F0',
                    'surface' => '#FFFFFF',
                    'text_primary' => '#1A1208',
                    'text_secondary' => '#6B5544',
                    'text_muted' => '#9C8875',
                ],
            ],
            'ocean' => [
                'name' => 'Ocean Breeze',
                'colors' => [
                    'primary' => '#2E4D8A',
                    'secondary' => '#4A72C4',
                    'accent' => '#6FA882',
                    'success' => '#4A7C59',
                    'warning' => '#F2A84E',
                    'danger' => '#C44B2B',
                    'background' => '#F0F4F8',
                    'surface' => '#FFFFFF',
                    'text_primary' => '#1E2D4A',
                    'text_secondary' => '#475569',
                    'text_muted' => '#94A3B8',
                ],
            ],
            'forest' => [
                'name' => 'Forest Green',
                'colors' => [
                    'primary' => '#4A7C59',
                    'secondary' => '#6FA882',
                    'accent' => '#B8D9C6',
                    'success' => '#4A7C59',
                    'warning' => '#E8872A',
                    'danger' => '#C44B2B',
                    'background' => '#EBF5EE',
                    'surface' => '#FFFFFF',
                    'text_primary' => '#1A3A2A',
                    'text_secondary' => '#2D5A3D',
                    'text_muted' => '#6B8B7A',
                ],
            ],
            'sunset' => [
                'name' => 'Desert Sunset',
                'colors' => [
                    'primary' => '#E8872A',
                    'secondary' => '#F2A84E',
                    'accent' => '#FDF0DE',
                    'success' => '#6FA882',
                    'warning' => '#D4A017',
                    'danger' => '#C44B2B',
                    'background' => '#FFF8F0',
                    'surface' => '#FFFFFF',
                    'text_primary' => '#3A2010',
                    'text_secondary' => '#6B4423',
                    'text_muted' => '#9C7A5A',
                ],
            ],
        ];
    }

    private function resetForm()
    {
        $this->selectedId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        
        // Reset to defaults
        $defaults = Theme::defaultColors();
        foreach ($defaults as $key => $value) {
            $this->$key = $value;
        }
        
        $this->resetErrorBag();
    }

    public function render()
    {
        $themes = Theme::latest()->paginate(12);
        $presets = $this->getPresets();

        return view('livewire.admin.themes-manager', [
            'themes' => $themes,
            'presets' => $presets,
        ]);
    }
}
