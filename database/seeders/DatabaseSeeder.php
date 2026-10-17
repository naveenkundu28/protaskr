<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Support\Roles;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Permissions are not seeded: they live in config/permissions.php.
     */
    public function run(): void
    {
        foreach (Roles::all() as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $user = User::updateOrCreate(
            ['email' => 'admin@ve.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('123456'),
            ]
        );

        if (!$user->isAdmin()) {
            $user->role()->associate(Role::where('name', Roles::ADMIN)->first())->save();
        }

        if (!$user->permissions()->exists()) {
            $user->unsetRelation('role');
            $user->assignDefaultPermissions();
        }
    }
}
