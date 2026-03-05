<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
         ];
         
         foreach ($permissions as $permission) {
              Permission::create(['name' => $permission]);
         }
 
         $user = User::create([
             'name' => 'Admin', 
             'email' => 'admin@gmail.com',
             'password' => bcrypt('123456')
         ]);
         
         $role = Role::create(['name' => 'Admin']);
          
         $permissions = Permission::pluck('id','id')->all();
        
         $role->syncPermissions($permissions);
          
         $user->assignRole([$role->id]);
 
         //roles
         $roles = [
             'Reviewer',
             'Employee',
             'Assignee',
         ];
 
         foreach ($roles as $role) {
             Role::firstOrCreate(['name' => $role]);
         }
    }
}
