<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define ALL Permissions from Documentation
        $permissions = [
            // Global (Super Admin only)
            'manage all organisations',
            'view platform stats',
            'impersonate users',
            'manage global library',

            // CMS / Content (Super Admin, CMS Editor)
            'ingest assets',
            'verify translations',
            'publish content',
            'tag heritage assets',
            'curate activities',

            // Organisation / School (Org Admin, Teacher)
            'manage organisation',
            'manage organisation users',
            'configure branding',
            'manage classes',
            'monitor student progress',
            'assign rewards',
            'view org analytics',

            // Household / Personal (Parent, Child)
            'manage child profiles',
            'view child progress',
            'set usage limits',
            'play activities',
            'earn badges',

            // Specialized (Kiosk Mode)
            'access kiosk activities',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Define ALL Roles as per the PDF Workflows
        $roles = [
            'super_admin' => $permissions, // Everything
            'org_admin' => [
                'manage organisation', 'manage organisation users', 'configure branding',
                'manage classes', 'monitor student progress', 'view org analytics',
                'ingest assets', 'publish content',
            ],
            'cms_editor' => [
                'ingest assets', 'verify translations', 'tag heritage assets', 'curate activities',
            ],
            'teacher' => [
                'manage classes', 'monitor student progress', 'assign rewards', 'play activities',
            ],
            'parent' => [
                'manage child profiles', 'view child progress', 'set usage limits', 'play activities',
            ],
            'child' => [
                'play activities', 'earn badges',
            ],
            'kiosk_mode' => [
                'access kiosk activities', 'play activities',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            /** @var Role $role */
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
