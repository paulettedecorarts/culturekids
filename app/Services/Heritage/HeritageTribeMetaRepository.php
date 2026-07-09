<?php

namespace App\Services\Heritage;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HeritageTribeMetaRepository
{
    /** @var array<string, array<string, mixed>>|null */
    private ?array $byName = null;

    /** @var array<string, array<string, mixed>>|null */
    private ?array $bySlug = null;

    /**
     * @return array<string, mixed>|null
     */
    public function forTribeName(string $name): ?array
    {
        $this->load();

        return $this->byName[$name] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function forSlug(string $slug): ?array
    {
        $this->load();

        return $this->bySlug[$slug] ?? null;
    }

    protected function load(): void
    {
        if ($this->byName !== null) {
            return;
        }

        $path = base_path('seed/activities.seed.json');

        if (! File::exists($path)) {
            $this->byName = [];
            $this->bySlug = [];

            return;
        }

        $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        $this->byName = [];
        $this->bySlug = [];

        foreach ($payload['tribes'] ?? [] as $tribe) {
            if (! is_array($tribe)) {
                continue;
            }

            $name = (string) ($tribe['name'] ?? '');
            $slug = (string) ($tribe['id'] ?? Str::slug($name));

            if ($name === '') {
                continue;
            }

            $this->byName[$name] = $tribe;
            $this->bySlug[$slug] = $tribe;
        }
    }
}
