<?php

namespace App\Livewire\Hooks;

use Illuminate\Pagination\Paginator;
use Livewire\ComponentHook;
use Livewire\Livewire;
use Livewire\WithPagination;

/**
 * Livewire stores pagination paths without a leading slash (e.g. "admin/activities").
 * Browser-relative resolution from /admin/activities then yields /admin/admin/activities.
 */
class AbsolutePaginationPath extends ComponentHook
{
    public function skip()
    {
        return ! in_array(WithPagination::class, class_uses_recursive($this->component));
    }

    public function boot()
    {
        Paginator::currentPathResolver(function () {
            $path = Livewire::originalPath();

            if (! is_string($path) || $path === '' || $path === 'POST') {
                return request()->getPathInfo() ?: '/';
            }

            return '/'.ltrim($path, '/');
        });
    }
}
