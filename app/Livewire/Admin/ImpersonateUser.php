<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.admin')]
class ImpersonateUser extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function impersonate($userId)
    {
        $targetUser = User::findOrFail($userId);
        
        // Don't allow impersonating yourself
        if ($targetUser->id === auth()->id()) {
            session()->flash('error', 'You cannot impersonate yourself.');
            return;
        }

        // Don't allow impersonating other super admins
        if ($targetUser->hasRole('super_admin')) {
            session()->flash('error', 'You cannot impersonate other super administrators.');
            return;
        }

        // Store the impersonator ID in session
        session(['impersonator_id' => auth()->id()]);
        session(['impersonating' => true]);
        session(['original_user_id' => auth()->id()]);

        // Log the impersonation
        AuditLog::record('IMPERSONATE', "users/{$targetUser->id}", [
            'target_user' => $targetUser->email,
            'target_role' => $targetUser->roles->pluck('name')->first(),
        ]);

        // Login as the target user
        Auth::login($targetUser);

        // Redirect to appropriate dashboard based on role
        if ($targetUser->hasRole('cms_editor')) {
            return redirect()->route('cms.editor.dashboard');
        } elseif ($targetUser->hasRole('org_admin')) {
            return redirect()->route('cms.admin.dashboard');
        } elseif ($targetUser->hasRole('teacher')) {
            return redirect()->route('teacher.dashboard');
        }

        return redirect()->route(\App\Support\PortalHome::dashboardRouteName($targetUser));
    }

    public function render()
    {
        $users = User::with(['roles', 'organisation'])
            ->where('id', '!=', auth()->id()) // Exclude current user
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->latest()
            ->paginate(20);

        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();

        return view('livewire.admin.impersonate-user', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
