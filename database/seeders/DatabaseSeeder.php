<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        {

            $adminRole = Role::create(['name' => 'admin']);
            $userRole = Role::create(['name' => 'user']);
        
            $permissions = ['create user', 'edit user', 'delete user', 'view user'];
            foreach ($permissions as $permission) {
                Permission::create(['name' => $permission]);
            }
        
            $adminRole->givePermissionTo(Permission::all());
        
            $admin = User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('password123')
            ]);
            $admin->assignRole('admin');
        
            $user = User::factory()->create([
                'name' => 'Shahboz',
                'email' => 'shahboz@gmail.com',
                'password' => bcrypt('20042004')
            ]);
            $user->assignRole('admin');
            }
    }
}
