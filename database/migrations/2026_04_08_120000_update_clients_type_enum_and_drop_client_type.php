<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'client_type')) {
                $table->dropColumn('client_type');
            }
        });

        if (Schema::hasColumn('clients', 'type')) {
            DB::statement("ALTER TABLE `clients` MODIFY `type` ENUM('internal_team','external_team','project','client') NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('clients', 'type')) {
            DB::statement("UPDATE `clients` SET `type` = 'internal_team' WHERE `type` = 'external_team'");
            DB::statement("ALTER TABLE `clients` MODIFY `type` ENUM('internal_team','project','client') NULL");
        }

        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'client_type')) {
                $table->enum('client_type', ['internal', 'external', 'other'])->default('internal');
            }
        });
    }
};

