<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = [
            'role-show',
            'role-create',
            'role-edit',
            'role-delete',
            'user-show',
            'user-create',
            'user-edit',
            'user-delete',
            'project-show',
            'project-create',
            'project-edit',
            'project-delete',
            'task-show',
            'task-create',
            'task-edit',
            'task-delete',
            'mark-task-complete',
            'service-show',
            'service-create',
            'service-edit',
            'service-delete',
         ];
         
         foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
         }
 
        $adminRole = Role::firstOrCreate(['name' => 'Admin']); 

        $roles = [
            'Reviewer',
            'Employee',
            'Assignee',
        ];
         
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        } 
          
        $permissions = Permission::all();
    
        $adminRole->syncPermissions($permissions);
        
        $user = User::updateOrCreate(
            ['email' => 'admin@ve.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('123456'),
            ]
        );

        if (!$user->hasRole('Admin')) {
            $user->assignRole('Admin');
        }
 
         //roles
         
 
         
    }
}
