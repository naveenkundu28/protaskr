<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * tracker_histories is the single source of truth for tracked time: a timer is
 * running when a history row is open, and the total is the sum of its rows.
 * The copies kept on tasks drifted out of sync, so they are removed.
 *
 * No tracked time is lost: time that only ever lived on a task is first moved into a history
 * row, and timers left "running" by the old status-change bug are closed at their last saved time.
 */
return new class extends Migration
{
    /**
     * An open row nobody has saved to for this long, on a task that is not flagged as running,
     * is a leftover of the old status-change bug rather than a live timer. (Live timers save every 10 seconds.)
     */
    private const STALE_MINUTES = 30;

    public function up(): void
    {
        if (Schema::hasColumn('tasks', 'elapsed_time')) {
            DB::transaction(function () {
                $this->moveTaskOnlyTimeIntoHistories();
                $this->closeAbandonedRows();
                $this->keepOneOpenRowPerUser();
            });

            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn(['start_time', 'end_time', 'elapsed_time', 'is_running']);
            });
        }

        Schema::table('tracker_histories', function (Blueprint $table) {
            // "is a timer running for this user / on this task?"
            $table->index(['user_id', 'is_running'], 'tracker_histories_user_running_index');
            $table->index(['task_id', 'is_running'], 'tracker_histories_task_running_index');
        });
    }

    /**
     * Brings the columns back and fills them from the history rows, which is all the old code needs.
     */
    public function down(): void
    {
        Schema::table('tracker_histories', function (Blueprint $table) {
            $table->dropIndex('tracker_histories_user_running_index');
            $table->dropIndex('tracker_histories_task_running_index');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('elapsed_time')->default(0);
            $table->boolean('is_running')->default(false);
        });

        DB::statement('UPDATE tasks SET
            start_time = (SELECT MAX(h.start_time) FROM tracker_histories h WHERE h.task_id = tasks.id),
            end_time = (SELECT MAX(h.end_time) FROM tracker_histories h WHERE h.task_id = tasks.id AND h.is_running = 0),
            is_running = CASE WHEN EXISTS (SELECT 1 FROM tracker_histories h WHERE h.task_id = tasks.id AND h.is_running = 1) THEN 1 ELSE 0 END,
            elapsed_time = COALESCE((SELECT MAX(h.elapsed_time) FROM tracker_histories h WHERE h.task_id = tasks.id AND h.is_running = 1), 0)');
    }

    /**
     * Time that exists only on the task row would vanish with the column, so give it a history row:
     *  - tasks tracked before tracker_histories existed (no history rows at all), and
     *  - tasks flagged running whose session has no open history row.
     * A task that already has history rows and is not running keeps them as the record.
     *
     * The time is moved, not copied: the task's elapsed_time is zeroed in the same transaction,
     * so running this again can never create a second row.
     */
    private function moveTaskOnlyTimeIntoHistories(): void
    {
        DB::table('tasks')
            ->where('elapsed_time', '>', 0)
            ->whereNotNull('user_id')
            ->whereExists(fn ($sub) => $sub->from('users')->whereColumn('users.id', 'tasks.user_id'))
            ->whereNotExists(fn ($sub) => $sub->from('tracker_histories')
                ->whereColumn('tracker_histories.task_id', 'tasks.id')
                ->where('tracker_histories.is_running', true))
            ->where(function ($query) {
                $query->where('is_running', true)
                    ->orWhereNotExists(fn ($sub) => $sub->from('tracker_histories')
                        ->whereColumn('tracker_histories.task_id', 'tasks.id'));
            })
            ->chunkById(500, function ($tasks) {
                $now = Carbon::now()->toDateTimeString();

                DB::table('tracker_histories')->insert($tasks->map(function ($task) use ($now) {
                    $start = Carbon::parse($task->start_time ?? $task->created_at);
                    $end = $start->copy()->addSeconds((int) $task->elapsed_time);

                    // a stopped task recorded when it ended; keep that unless it is impossible
                    if (!$task->is_running && $task->end_time && Carbon::parse($task->end_time)->gte($start)) {
                        $end = Carbon::parse($task->end_time);
                    }

                    return [
                        'user_id' => $task->user_id,
                        'task_id' => $task->id,
                        'start_time' => $start->toDateTimeString(),
                        'end_time' => $end->toDateTimeString(),
                        'elapsed_time' => (int) $task->elapsed_time,
                        'is_running' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all());

                DB::table('tasks')->whereIn('id', $tasks->pluck('id'))->update(['elapsed_time' => 0]);
            });
    }

    /**
     * The old status change cleared the task's running flag but left its history row open.
     * Close those rows at the last time anything was saved to them (start + saved elapsed).
     */
    private function closeAbandonedRows(): void
    {
        $cutoff = Carbon::now()->subMinutes(self::STALE_MINUTES)->toDateTimeString();

        DB::table('tracker_histories')
            ->where('is_running', true)
            ->whereNotNull('start_time')
            ->where(fn ($query) => $query->whereNull('updated_at')->orWhere('updated_at', '<', $cutoff))
            ->whereNotExists(fn ($sub) => $sub->from('tasks')
                ->whereColumn('tasks.id', 'tracker_histories.task_id')
                ->where('tasks.is_running', true))
            ->chunkById(500, function ($rows) {
                $rows->each(fn ($row) => $this->closeAtLastSavedTime($row));
            });
    }

    /**
     * A user can only have one timer running, so when several rows are open keep the newest.
     */
    private function keepOneOpenRowPerUser(): void
    {
        $userIds = DB::table('tracker_histories')
            ->where('is_running', true)
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            DB::table('tracker_histories')
                ->where('user_id', $userId)
                ->where('is_running', true)
                ->orderByDesc('start_time')
                ->orderByDesc('id')
                ->get()
                ->skip(1)
                ->each(fn ($row) => $this->closeAtLastSavedTime($row));
        }
    }

    private function closeAtLastSavedTime(object $row): void
    {
        DB::table('tracker_histories')->where('id', $row->id)->update([
            'is_running' => false,
            'end_time' => $row->start_time
                ? Carbon::parse($row->start_time)->addSeconds((int) $row->elapsed_time)->toDateTimeString()
                : null,
        ]);
    }
};
