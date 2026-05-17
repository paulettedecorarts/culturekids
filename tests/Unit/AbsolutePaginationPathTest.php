<?php

namespace Tests\Unit;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AbsolutePaginationPathTest extends TestCase
{
    public function test_paginator_urls_use_root_relative_paths(): void
    {
        $relative = new LengthAwarePaginator(range(1, 24), 24, 12, 1, [
            'path' => 'admin/activities',
        ]);

        $absolute = new LengthAwarePaginator(range(1, 24), 24, 12, 1, [
            'path' => '/admin/activities',
        ]);

        $this->assertSame('admin/activities?page=2', $relative->url(2));
        $this->assertSame('/admin/activities?page=2', $absolute->url(2));
    }
}
