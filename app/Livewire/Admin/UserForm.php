<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Organisation;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
            session()->flash('message', 'Platform account updated successfully.');
        } else {
            $newUser = User::create($data);
            $newUser->assignRole($this->userRoles);
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
