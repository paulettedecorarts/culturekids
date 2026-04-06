<?php

namespace App\Livewire\Admin;

use App\Models\Tribe;
use Livewire\Component;
use Livewire\WithPagination;

class TribeManager extends Component
{
    use WithPagination;

    public $name, $hero_name, $hero_emoji, $greeting, $region, $color;
    public $editingTribeId = null;
    public $showForm = false;

    protected $rules = [
        'name' => 'required|unique:tribes,name',
        'hero_name' => 'required',
        'hero_emoji' => 'nullable',
        'greeting' => 'nullable',
        'region' => 'nullable',
        'color' => 'nullable',
    ];

    public function render()
    {
        return view('livewire.admin.tribe-manager', [
            'tribes' => Tribe::latest()->paginate(10),
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate($this->editingTribeId ? [
            'name' => 'required|unique:tribes,name,' . $this->editingTribeId,
        ] : $this->rules);

        if ($this->editingTribeId) {
            $tribe = Tribe::find($this->editingTribeId);
            $tribe->update($this->getFormData());
        } else {
            Tribe::create($this->getFormData());
        }

        $this->resetForm();
        $this->dispatch('notify', 'Tribe saved successfully!');
    }

    public function edit($id)
    {
        $tribe = Tribe::find($id);
        $this->editingTribeId = $id;
        $this->name = $tribe->name;
        $this->hero_name = $tribe->hero_name;
        $this->hero_emoji = $tribe->hero_emoji;
        $this->greeting = $tribe->greeting;
        $this->region = $tribe->region;
        $this->color = $tribe->color;
        $this->showForm = true;
    }

    public function delete($id)
    {
        Tribe::find($id)->delete();
        $this->dispatch('notify', 'Tribe deleted.');
    }

    private function resetForm()
    {
        $this->name = $this->hero_name = $this->hero_emoji = $this->greeting = $this->region = $this->color = '';
        $this->editingTribeId = null;
        $this->showForm = false;
    }

    private function getFormData()
    {
        return [
            'name' => $this->name,
            'hero_name' => $this->hero_name,
            'hero_emoji' => $this->hero_emoji,
            'greeting' => $this->greeting,
            'region' => $this->region,
            'color' => $this->color,
        ];
    }
}
