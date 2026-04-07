<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Module;
use App\Models\AuditLog;

#[Layout('layouts.admin')]
class ModuleToggles extends Component
{
    public function toggle($id)
    {
        $module = Module::findOrFail($id);
        $module->is_enabled = !$module->is_enabled;
        $module->save();
        
        AuditLog::record('MODULE_TOGGLE', "modules/{$module->id}", [
            'module_key' => $module->key,
            'module_name' => $module->name,
            'status' => $module->is_enabled ? 'enabled' : 'disabled',
        ]);
        
        session()->flash('message', "Module '{$module->name}' " . ($module->is_enabled ? 'enabled' : 'disabled') . ' globally.');
    }

    public function render()
    {
        $modules = Module::orderBy('sort_order')->orderBy('name')->get();
        
        return view('livewire.admin.module-toggles', [
            'modules' => $modules,
        ]);
    }
}
