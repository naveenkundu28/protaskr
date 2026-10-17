<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permissions now live in config/permissions.php and each user's role in
     * users.role_id, so only the `roles` table is kept.
     */
    public function up(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('permissions');

        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_name_guard_name_unique');
            $table->dropColumn('guard_name');
            $table->unique('name');
        });
    }

    /**
     * Restores the roles table shape only. The dropped tables and their rows
     * are not recreated.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->string('guard_name')->default('web')->after('name');
            $table->unique(['name', 'guard_name']);
        });
    }
};
