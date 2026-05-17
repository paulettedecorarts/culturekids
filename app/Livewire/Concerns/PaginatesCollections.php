<?php

namespace App\Livewire\Concerns;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\WithPagination;

trait PaginatesCollections
{
    use WithPagination;

    protected function paginateCollection(Collection $items, int $perPage = 20, string $pageName = 'page'): LengthAwarePaginator
    {
        $page = max(1, (int) $this->getPage($pageName));
        $total = $items->count();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            [
                'path' => request()->getPathInfo() ?: '/',
                'pageName' => $pageName,
            ]
        );
    }
}
