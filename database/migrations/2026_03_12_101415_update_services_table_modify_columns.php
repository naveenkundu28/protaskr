<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop unique index from service_id
        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique(['service_id']);

            // Rename column
            $table->renameColumn('reason', 'cancel_reason');

            // add month
            $table->unsignedTinyInteger('month')->nullable()->after('hours');
        });
    }

    public function down(): void
    {
        // Add unique back
        Schema::table('services', function (Blueprint $table) {
            $table->unique('service_id');

            // Revert column name
            $table->renameColumn('cancel_reason', 'reason');

            $table->dropColumn('month');
        });
    }
};
