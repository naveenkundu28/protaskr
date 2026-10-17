<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links a client to its login (a users row with the Client role).
     * clients.user_id is already taken: it is the user who created the client.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('profile_user_id')->nullable()->after('user_id');
            $table->foreign('profile_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['profile_user_id']);
            $table->dropColumn('profile_user_id');
        });
    }
};
