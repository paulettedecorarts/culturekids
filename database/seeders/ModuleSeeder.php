<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Services\OrganisationModuleResolver;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (OrganisationModuleResolver::canonicalDefinitions() as $definition) {
            Module::query()->updateOrCreate(
                ['key' => $definition['key']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'icon' => $definition['icon'],
                    'sort_order' => $definition['sort_order'],
                    'is_enabled' => true,
                ]
            );
        }

        // Legacy key from early seeders — merged into `stories`.
        Module::query()->where('key', 'comics')->delete();

        $keys = collect(OrganisationModuleResolver::canonicalDefinitions())->pluck('key')->implode(', ');
        $this->command?->info("✅ Organisation modules seeded: {$keys}");
    }
}
