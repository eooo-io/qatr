<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Projects
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            // Test Plans
            'test-plans.view',
            'test-plans.create',
            'test-plans.update',
            'test-plans.delete',
            // Test Cases
            'test-cases.view',
            'test-cases.create',
            'test-cases.update',
            'test-cases.delete',
            // Execution
            'test-runs.view',
            'test-runs.execute',
            // Releases
            'releases.view',
            'releases.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Admin — full access
        Role::findOrCreate('admin')
            ->givePermissionTo(Permission::all());

        // Manager — can manage plans and runs, but not delete projects
        Role::findOrCreate('manager')
            ->givePermissionTo([
                'projects.view', 'projects.create', 'projects.update',
                'test-plans.view', 'test-plans.create', 'test-plans.update', 'test-plans.delete',
                'test-cases.view', 'test-cases.create', 'test-cases.update', 'test-cases.delete',
                'test-runs.view', 'test-runs.execute',
                'releases.view', 'releases.manage',
            ]);

        // Tester — can execute and manage test cases
        Role::findOrCreate('tester')
            ->givePermissionTo([
                'projects.view',
                'test-plans.view',
                'test-cases.view', 'test-cases.create', 'test-cases.update',
                'test-runs.view', 'test-runs.execute',
                'releases.view',
            ]);

        // Viewer — read-only
        Role::findOrCreate('viewer')
            ->givePermissionTo([
                'projects.view',
                'test-plans.view',
                'test-cases.view',
                'test-runs.view',
                'releases.view',
            ]);
    }
}
