<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Working resources are stored as comma-separated user ids in
 * services.working_resource (e.g. "3,7,12"). Moves every row of the
 * service_resources table back into that column, then drops the table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('services', 'working_resource')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('working_resource')->nullable()->after('front_resource');
            });
        }

        if (!Schema::hasTable('service_resources')) {
            return;
        }

        DB::table('service_resources')
            ->select('service_id', DB::raw('GROUP_CONCAT(user_id ORDER BY id SEPARATOR \',\') as user_ids'))
            ->groupBy('service_id')
            ->orderBy('service_id')
            ->get()
            ->each(function ($row) {
                DB::table('services')
                    ->where('id', $row->service_id)
                    ->update(['working_resource' => $row->user_ids]);
            });

        // Refuse to drop the table unless every resource made it into the column.
        $missing = DB::table('service_resources')
            ->join('services', 'services.id', '=', 'service_resources.service_id')
            ->whereRaw('FIND_IN_SET(service_resources.user_id, services.working_resource) = 0')
            ->count();

        if ($missing > 0) {
            throw new RuntimeException(
                "{$missing} service_resources row(s) were not copied to services.working_resource; refusing to drop the table."
            );
        }

        Schema::drop('service_resources');
    }

    public function down(): void
    {
        Schema::create('service_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['service_id', 'user_id']);
        });

        $now = now();
        $userIds = DB::table('users')->pluck('id')->flip();

        DB::table('services')
            ->whereNotNull('working_resource')
            ->where('working_resource', '!=', '')
            ->orderBy('id')
            ->select(['id', 'working_resource'])
            ->chunkById(500, function ($services) use ($now, $userIds) {
                $rows = [];
                foreach ($services as $service) {
                    foreach (array_unique(array_filter(explode(',', $service->working_resource))) as $userId) {
                        if ($userIds->has((int) $userId)) {
                            $rows[] = [
                                'service_id' => $service->id,
                                'user_id' => (int) $userId,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }
                DB::table('service_resources')->insert($rows);
            });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('working_resource');
        });
    }
};
