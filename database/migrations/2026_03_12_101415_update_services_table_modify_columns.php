<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (collect(DB::select("SHOW INDEX FROM `services` WHERE Key_name = 'services_service_id_unique'"))->isNotEmpty()) {
                $table->dropUnique(['service_id']);
            }
            // $table->unsignedTinyInteger('month')->nullable()->after('hours');
        });

        // Use CHANGE COLUMN for compatibility with MariaDB < 10.5.2
        DB::statement('ALTER TABLE `services` CHANGE `reason` `cancel_reason` TEXT NULL');
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unique('service_id');
            $table->dropColumn('month');
        });

        DB::statement('ALTER TABLE `services` CHANGE `cancel_reason` `reason` TEXT NULL');
    }
};
