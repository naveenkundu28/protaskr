<?php
  
namespace Database\Seeders;
  
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
  
class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $user = User::create([
        //     'name' => 'Admin', 
        //     'email' => 'admin@ve.com',
        //     'password' => bcrypt('123456')
        // ]);
        
        // $role = Role::create(['name' => 'Admin']);

        $user = User::firstOrCreate(
            ['email' => 'admin@ve.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('123456')
            ]
        );

        $role = Role::firstOrCreate(['name' => 'Admin']);
         
        $permissions = Permission::pluck('id','id')->all();
       
        $role->syncPermissions($permissions);
         
        // $user->assignRole([$role->id]);
        if (!$user->hasRole('Admin')) {
            $user->assignRole($role);
        }
    }
}
