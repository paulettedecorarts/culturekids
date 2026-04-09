<?php

namespace App\Livewire\Admin;

use App\Models\Language;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class LanguagesManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Language::query()
            ->when($this->search !== '', function ($q): void {
                $q->where(function ($inner): void {
                    $term = '%'.$this->search.'%';
                    $inner->where('name', 'like', $term)
                        ->orWhere('native_name', 'like', $term)
                        ->orWhere('code', 'like', $term);
                });
            })
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('sort_order')
            ->orderBy('name');

        $total = Language::count();
        $active = Language::where('is_active', true)->count();
        $audio = Language::where('audio_pack_available', true)->count();
        $avgCoverage = (int) round((float) Language::avg('translation_coverage'));

        return view('livewire.admin.languages-manager', [
            'languages' => $query->paginate(12),
            'stats' => [
                'total' => $total,
                'active' => $active,
                'audio' => $audio,
                'avg_coverage' => $avgCoverage,
            ],
        ]);
    }
}
