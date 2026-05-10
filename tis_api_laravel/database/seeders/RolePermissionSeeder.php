<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view dashboard',
            'manage users',
            'view failures',
            'manage failures',
            'view files',
            'manage files',
            'export data',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $operatorRole = Role::firstOrCreate(['name' => 'operator']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);

        $superadminRole->syncPermissions($permissions);
        $adminRole->syncPermissions($permissions);
        $operatorRole->syncPermissions(['view dashboard', 'view failures', 'manage failures', 'view files', 'export data']);
        $viewerRole->syncPermissions(['view dashboard', 'view failures', 'view files']);

        // Superadmin user (tiomuhamadnur@gmail.com)
        $superadmin = User::firstOrCreate(
            ['email' => 'tiomuhamadnur@gmail.com'],
            [
                'name' => 'Tio Muhamad Nur',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $superadmin->syncRoles(['superadmin']);

        // Default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@tisgateway.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        $operator = User::firstOrCreate(
            ['email' => 'operator@tisgateway.com'],
            [
                'name' => 'Operator User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $operator->syncRoles(['operator']);

        $viewer = User::firstOrCreate(
            ['email' => 'viewer@tisgateway.com'],
            [
                'name' => 'Viewer User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $viewer->syncRoles(['viewer']);
    }
}
