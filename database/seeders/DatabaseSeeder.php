<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // First Super Admin
        $superAdmin = User::create([
            'name' => 'Cosmah Admin',
            'email' => 'admin@culturekids.app',
            'password' => bcrypt('password'),
        ]);
        $superAdmin->assignRole('super_admin');

        // Initial organisation for parent demo
        $org = \App\Models\Organisation::create([
            'name' => 'Legacy Primary School',
            'slug' => 'legacy-primary',
            'plan' => 'free',
        ]);

        // Demo Parent
        $parent = User::create([
            'name' => 'Tendo Parent',
            'email' => 'parent@culturekids.app',
            'password' => bcrypt('password'),
            'organisation_id' => $org->id,
        ]);
        $parent->assignRole('parent');
    }
}
