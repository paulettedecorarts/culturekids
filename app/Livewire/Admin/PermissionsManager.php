<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

#[Layout('layouts.admin')]
class PermissionsManager extends Component
{
    public function render()
    {
        // Load roles with their permissions
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return view('livewire.admin.permissions-manager', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
}
