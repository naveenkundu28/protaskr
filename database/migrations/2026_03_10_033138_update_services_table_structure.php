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
            // update existing values first
            // 1. extend enum first (include both old + new values)
            DB::statement("
            ALTER TABLE services 
            MODIFY service_status 
            ENUM(
                'new',
                'continuing',
                'closed',
                'in_progress',
                'completed',
                'replacement',
                'canceled'
            ) NOT NULL
        ");

            // 2. convert existing values
            DB::statement("
            UPDATE services 
            SET service_status = 'in_progress' 
            WHERE service_status = 'continuing'
        ");

            DB::statement("
            UPDATE services 
            SET service_status = 'completed' 
            WHERE service_status = 'closed'
        ");

            // 3. remove old values
            DB::statement("
            ALTER TABLE services 
            MODIFY service_status 
            ENUM(
                'new',
                'in_progress',
                'completed',
                'replacement',
                'canceled'
            ) NOT NULL
        ");


            $table->foreignId('front_resource')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('working_resource')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // 1. extend enum again to include both old + new values
            DB::statement("
                ALTER TABLE services 
                MODIFY service_status 
                ENUM(
                    'new',
                    'continuing',
                    'closed',
                    'in_progress',
                    'completed',
                    'replacement',
                    'canceled'
                ) NOT NULL
            ");

            // 2. revert data
            DB::statement("
                UPDATE services 
                SET service_status = 'continuing' 
                WHERE service_status = 'in_progress'
            ");

            DB::statement("
                UPDATE services 
                SET service_status = 'closed' 
                WHERE service_status = 'completed'
            ");

            DB::statement("
                UPDATE services 
                SET service_status = 'closed' 
                WHERE service_status IN ('replacement','canceled')
            ");

            // 3. shrink enum back to original
            DB::statement("
                ALTER TABLE services 
                MODIFY service_status 
                ENUM(
                    'new',
                    'continuing',
                    'closed'
                ) NOT NULL
            ");

            $table->dropForeign(['front_resource']);
            $table->dropForeign(['working_resource']);

            $table->dropColumn([
                'front_resource',
                'working_resource',
                'reason'
            ]);
        });
    }
};
