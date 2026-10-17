<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each user now holds exactly one role, stored on the user.
     * Existing assignments are copied over from model_has_roles.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('id');
            $table->foreign('role_id')->references('id')->on('roles')->restrictOnDelete();
        });

        if (Schema::hasTable('model_has_roles')) {
            $assignments = DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->orderBy('role_id')
                ->get(['model_id', 'role_id']);

            foreach ($assignments as $assignment) {
                DB::table('users')
                    ->where('id', $assignment->model_id)
                    ->whereNull('role_id')
                    ->update(['role_id' => $assignment->role_id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
