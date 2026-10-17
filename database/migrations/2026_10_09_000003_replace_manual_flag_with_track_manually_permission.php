<?php

use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * users.manual ("Allow Manual Tracking") becomes the per-user permission
     * tasks-track-manually. Users whose role has it by default receive it, and
     * so does everyone who had the flag switched on.
     */
    public function up(): void
    {
        UserPermission::grantToDefaultRoles(['tasks-track-manually']);

        $now = now();

        foreach (DB::table('users')->where('manual', true)->pluck('id') as $userId) {
            DB::table('user_permissions')->insertOrIgnore([
                'user_id' => $userId,
                'permission' => 'tasks-track-manually',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('manual');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('manual')->default(false);
        });

        DB::table('users')
            ->whereIn('id', DB::table('user_permissions')->where('permission', 'tasks-track-manually')->pluck('user_id'))
            ->update(['manual' => true]);

        DB::table('user_permissions')->where('permission', 'tasks-track-manually')->delete();
    }
};
