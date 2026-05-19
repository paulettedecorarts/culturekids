<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organisation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure Roles & Permissions are populated first
        $this->call(RoleSeeder::class);
        $this->call(ModuleSeeder::class);

        // --- 1. Organisations ---
        // $naluwoozaOrg = Organisation::firstOrCreate([
        //     'slug' => 'naluwooza-org',
        // ], [
        //     'name' => 'Naluwooza Org',
        //     'plan' => 'enterprise',
        // ]);

        // $ugSchoolsOrg = Organisation::firstOrCreate([
        //     'slug' => 'ug-schools',
        // ], [
        //     'name' => 'Uganda Schools Org',
        //     'plan' => 'school',
        // ]);

        // --- 2. Test Credentials ---

        // #1: Super Admin
        $superAdmin = User::firstOrCreate([
            'email' => 'admin@culturekids.app',
        ], [
            'name' => 'Super Admin',
            'password' => bcrypt('password'),
        ]);
        $superAdmin->assignRole('super_admin');

        // // #2: Org Admin
        // $orgAdmin = User::firstOrCreate([
        //     'email' => 'jane@naluwooza.ug',
        // ], [
        //     'name' => 'Jane (Org Admin)',
        //     'password' => bcrypt('password'),
        //     'organisation_id' => $naluwoozaOrg->id,
        // ]);
        // $orgAdmin->assignRole('org_admin');

        // #3: CMS Editor
        // $cmsEditor = User::firstOrCreate([
        //     'email' => 'editor@culturekids.app',
        // ], [
        //     'name' => 'Content Editor',
        //     'password' => bcrypt('password'),
        // ]);
        // $cmsEditor->assignRole('cms_editor');

        // #4: Teacher
        // $teacher = User::firstOrCreate([
        //     'email' => 'teacher@ugschools.ug',
        // ], [
        //     'name' => 'Teacher',
        //     'password' => bcrypt('password'),
        //     'organisation_id' => $ugSchoolsOrg->id,
        // ]);
        // $teacher->assignRole('teacher');

        // // #5: Parent
        // $parent = User::firstOrCreate([
        //     'email' => 'parent@home.ug',
        // ], [
        //     'name' => 'Demo Parent',
        //     'password' => bcrypt('password'),
        // ]);
        // $parent->assignRole('parent');

        $this->command->info('✅ Test credentials successfully seeded!');

        // Sync existing content to the activities table
        // These are idempotent — safe to run on every deployment
        $this->call(SyncComicsToActivitiesSeeder::class);
    }
}
