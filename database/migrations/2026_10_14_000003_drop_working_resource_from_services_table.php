<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Refuse to drop the column unless every old value is present in the new table.
        $missing = DB::table('services')
            ->whereNotNull('working_resource')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('service_resources')
                    ->whereColumn('service_resources.service_id', 'services.id')
                    ->whereColumn('service_resources.user_id', 'services.working_resource');
            })
            ->count();

        if ($missing > 0) {
            throw new RuntimeException(
                "{$missing} service(s) have a working_resource that is not in service_resources; refusing to drop the column."
            );
        }

        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['working_resource']);
            $table->dropColumn('working_resource');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('working_resource')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });

        // Restore the first resource of each service into the single column.
        DB::statement('
            UPDATE services
            SET working_resource = (
                SELECT user_id FROM service_resources
                WHERE service_resources.service_id = services.id
                ORDER BY service_resources.id
                LIMIT 1
            )
        ');
    }
};
