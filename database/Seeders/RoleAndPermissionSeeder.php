<?php

namespace Portable\FilaCms\Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create roles
        $roles = [
            ['name' => 'Admin'],
            ['name' => 'User'],
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role['name']);
        }

        // Create permissions
        $permissions = [
            ['name' => 'access filacms-backend'],
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission['name']);
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'Admin')->first();
        $userRole = Role::where('name', 'User')->first();

        $adminPermissions = Permission::all();

        $adminRole->permissions()->attach($adminPermissions);
    }
}
