<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use Livewire\Component;
use App\Models\Tribe;
use Illuminate\Validation\Rule;

class TribeForm extends Component
{
    use UsesPortalContext;

    public $tribe;
    public $editing = false;

    // Form fields
    public $name;
    public $hero_name;
    public $hero_emoji;
    public $hero_icon;
    public $greeting;
    public $region;
    public $color = '#7C3AED';

    public function mount(?Tribe $tribe = null)
    {
        if ($tribe && $tribe->exists) {
            $this->tribe = $tribe;
            $this->editing = true;
            $this->name = $tribe->name;
            $this->hero_name = $tribe->hero_name;
            $this->hero_emoji = $tribe->hero_emoji;
            $this->hero_icon = $tribe->hero_icon;
            $this->greeting = $tribe->greeting;
            $this->region = $tribe->region;
            $this->color = $tribe->color;
        }
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'min:2', 'max:50', Rule::unique('tribes')->ignore($this->tribe?->id)],
            'hero_name' => 'required|min:2|max:50',
            'hero_emoji' => 'required',
            'hero_icon' => 'nullable',
            'greeting' => 'required|min:2|max:100',
            'region' => 'required',
            'color' => 'required',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'hero_name' => $this->hero_name,
            'hero_emoji' => $this->hero_emoji,
            'hero_icon' => $this->hero_icon,
            'greeting' => $this->greeting,
            'region' => $this->region,
            'color' => $this->color,
        ];

        if ($this->editing) {
            $this->tribe->update($data);
            session()->flash('message', 'Heritage record updated.');
        } else {
            Tribe::create($data);
            session()->flash('message', 'New Heritage Tribe added.');
        }

        return redirect()->route($this->portalRouteName('tribes'));
    }

    public function render()
    {
        return view('livewire.admin.tribe-form', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
