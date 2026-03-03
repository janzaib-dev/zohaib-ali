<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create super-admin role
        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        
        // We will assign all existing permissions just in case.
        $role->syncPermissions(Permission::all());

        // Create Super Admin User
        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('808080') // Updated password
            ]
        );

        $user->assignRole($role);
    }
}
