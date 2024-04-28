<?php

namespace Portable\FilaCms\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            ['name' => 'view users'],
            ['name' => 'manage users'],
            ['name' => 'view authors'],
            ['name' => 'manage authors'],
            ['name' => 'view roles'],
            ['name' => 'manage roles'],
            ['name' => 'view permissions'],
            ['name' => 'manage permissions'],
            ['name' => 'view taxonomies'],
            ['name' => 'manage taxonomies'],
            ['name' => 'view pages'],
            ['name' => 'manage pages'],
            ['name' => 'view settings'],
            ['name' => 'manage settings'],
            ['name' => 'view menus'],
            ['name' => 'manage menus'],
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission['name']);
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'Admin')->first();
        $userRole = Role::where('name', 'User')->first();

        $adminPermissions = Permission::all();

        $adminRole->permissions()->sync($adminPermissions);
    }
}
