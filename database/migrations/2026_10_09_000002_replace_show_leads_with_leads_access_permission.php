<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * users.show_leads picked the people who work with leads. That is now the
     * per-user leads-access permission, so flagged users receive it and the
     * column goes away.
     */
    public function up(): void
    {
        $now = now();

        foreach (DB::table('users')->where('show_leads', true)->pluck('id') as $userId) {
            DB::table('user_permissions')->insertOrIgnore([
                'user_id' => $userId,
                'permission' => 'leads-access',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('show_leads');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('show_leads')->default(false);
        });

        DB::table('users')
            ->whereIn('id', DB::table('user_permissions')->where('permission', 'leads-access')->pluck('user_id'))
            ->update(['show_leads' => true]);
    }
};
