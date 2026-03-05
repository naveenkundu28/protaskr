<?php
  
namespace Database\Seeders;
  
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
  
class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
           'mark-task-complete'
        ];
        
        foreach ($permissions as $permission) {
             Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
