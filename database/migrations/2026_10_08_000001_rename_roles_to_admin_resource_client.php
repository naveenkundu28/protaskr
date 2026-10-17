<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Role set becomes Admin, Resource, Client:
     *   Employee -> Resource, Reviewer -> Client, Assignee removed.
     */
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('roles')->where('name', 'Employee')->update(['name' => 'Resource']);
            DB::table('roles')->where('name', 'Reviewer')->update(['name' => 'Client']);

            $assigneeIds = DB::table('roles')->where('name', 'Assignee')->pluck('id');
            if ($assigneeIds->isNotEmpty()) {
                DB::table('model_has_roles')->whereIn('role_id', $assigneeIds)->delete();
                DB::table('role_has_permissions')->whereIn('role_id', $assigneeIds)->delete();
                DB::table('roles')->whereIn('id', $assigneeIds)->delete();
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            DB::table('roles')->where('name', 'Resource')->update(['name' => 'Employee']);
            DB::table('roles')->where('name', 'Client')->update(['name' => 'Reviewer']);

            if (!DB::table('roles')->where('name', 'Assignee')->exists()) {
                DB::table('roles')->insert([
                    'name' => 'Assignee',
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
};
