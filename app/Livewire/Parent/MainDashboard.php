<?php

namespace App\Livewire\Parent;

use App\Support\ChildProfileAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.parent')]
class MainDashboard extends Component
{
    public string $parentName = '';

    /** @var array<int, array{attainment: string, label: string}> */
    public array $stats = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->parentName = $user?->name ? (string) $user->name : __('Parent');

        $children = $user
            ? ChildProfileAccess::queryFor($user)->orderBy('name')->get()
            : collect();

        $totalStars = (int) $children->sum('total_stars');

        $this->stats = [
            ['attainment' => (string) $children->count(), 'label' => __('Child profiles')],
            ['attainment' => (string) $totalStars, 'label' => __('Total stars earned')],
            ['attainment' => $children->isEmpty() ? '—' : __('Active'), 'label' => __('Family learning')],
        ];
    }

    public function render()
    {
        $children = ChildProfileAccess::queryFor(auth()->user())->orderBy('name')->get();

        return view('livewire.parent.main-dashboard', [
            'children' => $children,
        ]);
    }
}
