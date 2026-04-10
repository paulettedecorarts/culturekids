<?php

namespace App\Livewire\Admin;

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

    public $userRoles = [];

    public function mount(?User $user = null)
    {
        if ($user && $user->exists) {
            $this->user = $user;
            $this->editing = true;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->organisation_id = $user->organisation_id;
            $this->userRoles = $user->roles->pluck('name')->toArray();
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
            'password' => $this->editing ? 'nullable|min:8' : 'required|min:8',
            'organisation_id' => 'nullable|exists:organisations,id',
            'userRoles' => 'required|array|min:1',
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

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editing) {
            $this->user->update($data);
            $this->user->syncRoles($this->userRoles);

            // Log the update
            AuditLog::record('UPDATE', "users/{$this->user->id}", [
                'user_email' => $this->email,
                'roles' => $this->userRoles,
            ]);

            session()->flash('message', 'Platform account updated successfully.');
        } else {
            $newUser = User::create($data);
            $newUser->assignRole($this->userRoles);

            // Log the creation
            AuditLog::record('CREATE', "users/{$newUser->id}", [
                'user_email' => $this->email,
                'roles' => $this->userRoles,
            ]);

            session()->flash('message', 'Platform account created successfully.');
        }

        return redirect()->route('admin.users');
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
