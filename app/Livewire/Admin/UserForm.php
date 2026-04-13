<?php

namespace App\Livewire\Admin;

use App\Jobs\SendPasswordResetEmail;
use App\Models\AuditLog;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('layouts.admin')]
class UserForm extends Component
{
    public $user; // Existing model for editing

    public $editing = false;

    // Form fields
    public $name;

    public $email;

    public $password;

    public $organisation_id;

    public $selectedRole;

    public function mount(?User $user = null)
    {
        if ($user && $user->exists) {
            $this->user = $user;
            $this->editing = true;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->organisation_id = $user->organisation_id;
            $this->selectedRole = $user->roles->first()?->name;
        } elseif (request()->filled('organisation_id')) {
            $oid = (int) request('organisation_id');
            if (Organisation::whereKey($oid)->exists()) {
                $this->organisation_id = $oid;
            }
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|min:3|max:100',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user?->id)],
            'organisation_id' => 'nullable|exists:organisations,id',
            'selectedRole' => 'required|string',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'organisation_id' => $this->organisation_id,
        ];

        try {
            if ($this->editing) {
                $this->user->update($data);
                $this->user->syncRoles([$this->selectedRole]);

                // Log the update
                AuditLog::record('UPDATE', "users/{$this->user->id}", [
                    'user_email' => $this->email,
                    'role' => $this->selectedRole,
                ]);

                session()->flash('message', 'Account updated successfully.');
                return redirect()->route('admin.users');
            } else {
                // Create new user with temporary password
                $data['password'] = Hash::make(uniqid());
                $data['email_verified_at'] = now();
                
                $newUser = User::create($data);
                $newUser->assignRole($this->selectedRole);

                // Log the creation
                AuditLog::record('CREATE', "users/{$newUser->id}", [
                    'user_email' => $this->email,
                    'role' => $this->selectedRole,
                ]);

                // Queue password reset email (non-blocking)
                SendPasswordResetEmail::dispatch($this->email);
                
                session()->flash('message', 'Account created successfully. Password reset email will be sent to ' . $this->email);
                
                // Clear form
                $this->reset(['name', 'email', 'organisation_id', 'selectedRole']);
                $this->resetValidation();
                
                return redirect()->route('admin.users');
            }
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Failed to save account. Please try again.');
        }
    }

    public function render()
    {
        $roles = Role::orderBy('name')->get();
        $organisations = Organisation::orderBy('name')->get();

        return view('livewire.admin.user-form', [
            'roles' => $roles,
            'organisations' => $organisations,
        ]);
    }
}
