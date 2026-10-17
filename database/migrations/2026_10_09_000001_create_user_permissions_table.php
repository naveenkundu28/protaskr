<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permissions are now held per user. config/permissions.php stays the
     * list of every permission and of each role's defaults; existing users
     * receive the defaults of their current role.
     */
    public function up(): void
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('permission');
            $table->timestamps();

            $table->unique(['user_id', 'permission']);
        });

        $roles = DB::table('roles')->pluck('name', 'id');
        $now = now();
        $rows = [];

        foreach (DB::table('users')->whereNotNull('role_id')->get(['id', 'role_id']) as $user) {
            $role = $roles[$user->role_id] ?? null;

            foreach (config('permissions', []) as $permission => $allowedRoles) {
                if (in_array($role, $allowedRoles, true)) {
                    $rows[] = [
                        'user_id' => $user->id,
                        'permission' => $permission,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('user_permissions')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
