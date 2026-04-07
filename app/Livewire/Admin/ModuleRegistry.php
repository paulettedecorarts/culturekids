<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Module;
use App\Models\AuditLog;

#[Layout('layouts.admin')]
class ModuleRegistry extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editing = false;
    public $showIconPicker = false;
    
    // Form fields
    public $selectedId;
    public $key;
    public $name;
    public $description;
    public $icon;
    public $is_enabled = true;
    public $sort_order = 0;

    // Available icons for modules
    public $availableIcons = [
        '📚', '📖', '📕', '📗', '📘', '📙', '📔', '📓', '📒', '📃', '📄', '📰', '🗞️',
        '🎵', '🎶', '🎤', '🎧', '🎼', '🎹', '🥁', '🎺', '🎸', '🪕',
        '🌍', '🌎', '🌏', '🗺️', '🧭', '🏛️', '🏰', '🏯', '🕌', '⛩️',
        '🎨', '🖼️', '🖌️', '🎭', '🎪', '🎬', '🎮', '🕹️', '🎲', '🧩',
        '👥', '👨‍🏫', '👩‍🏫', '👨‍🎓', '👩‍🎓', '👶', '🧒', '👦', '👧',
        '💰', '💳', '💵', '💴', '💶', '💷', '🪙',
        '🖥️', '💻', '📱', '⌨️', '🖨️', '📠',
        '🗣️', '💬', '💭', '🗨️', '💡', '🔔', '📢', '📣',
        '✏️', '✒️', '🖊️', '🖍️', '📝', '📋', '📌', '📍', '🔖',
        '🏫', '🏢', '🏛️', '🏪', '🏬', '🏭', '🏗️',
        '⭐', '🌟', '✨', '💫', '🔥', '🎯', '🎁', '🏆', '🥇', '🥈', '🥉',
    ];

    public function selectIcon($icon)
    {
        $this->icon = $icon;
        $this->showIconPicker = false;
    }

    public function updatedName($value)
    {
        if (!$this->editing) {
            $this->key = \Illuminate\Support\Str::slug($value, '_');
        }
    }

    protected $rules = [
        'key' => 'required|alpha_dash|unique:modules,key',
        'name' => 'required|min:3|max:100',
        'description' => 'nullable|max:500',
        'icon' => 'nullable|max:10',
        'is_enabled' => 'boolean',
        'sort_order' => 'integer|min:0',
    ];

    public function create()
    {
        $this->resetForm();
        $this->editing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $module = Module::findOrFail($id);
        $this->selectedId = $id;
        $this->key = $module->key;
        $this->name = $module->name;
        $this->description = $module->description;
        $this->icon = $module->icon;
        $this->is_enabled = $module->is_enabled;
        $this->sort_order = $module->sort_order;
        
        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editing) {
            $rules['key'] = 'required|alpha_dash|unique:modules,key,' . $this->selectedId;
        }

        $this->validate($rules);

        $data = [
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'is_enabled' => $this->is_enabled,
            'sort_order' => $this->sort_order,
        ];

        if ($this->editing) {
            $module = Module::findOrFail($this->selectedId);
            $module->update($data);
            
            AuditLog::record('UPDATE', "modules/{$module->id}", [
                'module_key' => $this->key,
                'module_name' => $this->name,
            ]);
            
            session()->flash('message', 'Module updated successfully.');
        } else {
            $module = Module::create($data);
            
            AuditLog::record('CREATE', "modules/{$module->id}", [
                'module_key' => $this->key,
                'module_name' => $this->name,
            ]);
            
            session()->flash('message', 'Module created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        $module = Module::findOrFail($id);
        
        AuditLog::record('DELETE', "modules/{$module->id}", [
            'module_key' => $module->key,
            'module_name' => $module->name,
        ]);
        
        $module->delete();
        session()->flash('message', 'Module removed successfully.');
    }

    public function toggleGlobal($id)
    {
        $module = Module::findOrFail($id);
        $module->is_enabled = !$module->is_enabled;
        $module->save();
        
        AuditLog::record('MODULE_TOGGLE', "modules/{$module->id}", [
            'module_key' => $module->key,
            'status' => $module->is_enabled ? 'enabled' : 'disabled',
        ]);
    }

    private function resetForm()
    {
        $this->selectedId = null;
        $this->key = '';
        $this->name = '';
        $this->description = '';
        $this->icon = '';
        $this->is_enabled = true;
        $this->sort_order = 0;
        $this->showIconPicker = false;
        $this->resetErrorBag();
    }

    public function render()
    {
        $modules = Module::orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('livewire.admin.module-registry', [
            'modules' => $modules,
        ]);
    }
}
