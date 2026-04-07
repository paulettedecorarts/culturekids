<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\AuditLog;

#[Layout('layouts.admin')]
class AuditLogs extends Component
{
    use WithPagination;

    public $search = '';
    public $actionFilter = '';
    public $dateFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingActionFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditLog::with(['user', 'impersonator'])
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('action', 'like', '%' . $this->search . '%')
                      ->orWhere('resource', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function($userQuery) {
                          $userQuery->where('email', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->actionFilter, function ($query) {
                $query->where('action', $this->actionFilter);
            })
            ->when($this->dateFilter, function ($query) {
                $date = now()->parse($this->dateFilter);
                $query->whereDate('created_at', $date);
            })
            ->latest()
            ->paginate(50);

        $actions = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('livewire.admin.audit-logs', [
            'logs' => $logs,
            'actions' => $actions,
        ]);
    }
}
