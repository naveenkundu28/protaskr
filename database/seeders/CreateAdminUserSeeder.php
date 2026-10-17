<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Support\Roles;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => Roles::ADMIN]);

        $user = User::firstOrCreate(
            ['email' => 'admin@ve.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('123456')
            ]
        );

        if (!$user->isAdmin()) {
            $user->role()->associate($role)->save();
        }

        if (!$user->permissions()->exists()) {
            $user->unsetRelation('role');
            $user->assignDefaultPermissions();
        }
    }
}
