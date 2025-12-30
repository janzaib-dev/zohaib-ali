<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run other seeders
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            WarehouseSeeder::class,
        ]);

        // Create or get users
        $branchUser = User::firstOrCreate(
            ['email' => 'soban@soban.com'],
            [
                'name' => 'soban',
                'password' => Hash::make('soban'),
            ]
        );

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin'),
            ]
        );

        // Define permissions
        $permissions = [
            'Create Product',
            'Delete Product',
            'View Product',
            'Edit Product',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $branchRole = Role::firstOrCreate(['name' => 'branch']);

        // Assign permissions to roles
        $adminRole->syncPermissions($permissions);
        $branchRole->syncPermissions($permissions);

        // Assign roles to users
        if ($adminUser) {
            $adminUser->assignRole($adminRole);
        }
        if ($branchUser) {
            $branchUser->assignRole($branchRole);
        }
    }
}
