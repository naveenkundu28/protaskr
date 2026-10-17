<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Carries every existing services.working_resource value over to the new
 * service_resources table. Nothing is merged, changed or deleted in services:
 * each service that has a working resource gets exactly one service_resources
 * row for it (soft-deleted services included). Safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('services')
            ->whereNotNull('working_resource')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('service_resources')
                    ->whereColumn('service_resources.service_id', 'services.id')
                    ->whereColumn('service_resources.user_id', 'services.working_resource');
            })
            ->orderBy('id')
            ->select(['id', 'working_resource'])
            ->chunkById(500, function ($services) use ($now) {
                DB::table('service_resources')->insert(
                    $services->map(fn ($service) => [
                        'service_id' => $service->id,
                        'user_id' => $service->working_resource,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            });
    }

    public function down(): void
    {
        // The old column still holds the data at this point, so the rows can simply go.
        DB::table('service_resources')->delete();
    }
};
