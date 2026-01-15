<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'tickets.view_all',
            'tickets.view_department',
            'tickets.assign',
            'tickets.reply',
            'tickets.update_status',
            'tickets.delete',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'departments.view',
            'departments.create',
            'departments.edit',
            'departments.delete',
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $agentRole = Role::firstOrCreate(['name' => 'agent']);
        $agentRole->givePermissionTo([
            'tickets.view_department',
            'tickets.reply',
            'tickets.update_status',
            'departments.view',
            'categories.view',
        ]);
    }
}
