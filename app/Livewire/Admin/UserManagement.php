<?php

namespace App\Livewire\Admin;

use App\Jobs\SendPasswordResetEmail;
use App\Models\AuditLog;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.admin')]
class UserManagement extends Component
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

    public function resendSetupEmail($userId)
    {
        $user = User::findOrFail($userId);
        
        // Queue password reset email
        SendPasswordResetEmail::dispatch($user->email);
        
        // Log the action
        AuditLog::record('RESEND_SETUP_EMAIL', "users/{$user->id}", [
            'user_email' => $user->email,
            'user_name' => $user->name,
        ]);
        
        session()->flash('message', 'Setup email sent to ' . $user->email);
    }

    public function delete($id)
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        $user = User::findOrFail($id);
        
        // Log the deletion
        AuditLog::record('DELETE', "users/{$user->id}", [
            'user_email' => $user->email,
            'user_name' => $user->name,
        ]);
        
        $user->delete();
        session()->flash('message', 'User removed from platform.');
    }

    public function render()
    {
        $users = User::with(['roles', 'organisation'])
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
            ->paginate(15);

        $roles = Role::orderBy('name')->get();

        return view('livewire.admin.user-management', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
