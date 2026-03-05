<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\TrackerHistory;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\form;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

class TasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $taskId = text('Enter Task ID:');

        $task = Task::find($taskId);

        $taskDetails = [
            ['project', $task->project->name],
            ['title', $task->title],
            ['status', $task->status],
            ['start_time', $task->start_time],
            ['end_time', $task->end_time],
            ['elapsed_time', $task->elapsed_time],
        ];

        table(['Key', 'Value'], $taskDetails);


        $editTask = confirm(
            label: 'Do you want to edit?',
            default: false,
        );

        if ($editTask) {
            $responses = form()
                ->text(label: 'Start Time?', name: 'start_time', default: $task->start_time, required: true)
                ->text(label: 'End Time?', name: 'end_time', default: $task->end_time, required: true)
                ->text(label: 'Elapsed Time?', name: 'elapsed_time', default: $task->elapsed_time, required: true)
                ->submit();

            $task->update($responses);


            $taskDetails = [
                ['project', $task->project->name],
                ['title', $task->title],
                ['status', $task->status],
                ['start_time', $task->start_time],
                ['end_time', $task->end_time],
                ['elapsed_time', $task->elapsed_time],
            ];

            table(['Key', 'Value'], $taskDetails);
        }

        $histories = TrackerHistory::select('id', 'start_time', 'end_time', 'elapsed_time', 'is_running')->where('task_id', $taskId)->get();

        info('Task Histories:');

        table(['ID', 'Start Time', 'End Time', 'Elapsed Time', 'Is Running'], $histories->toArray());

        $this->updateHistory($histories, $taskId);
    }

    public function updateHistory($histories, $taskId)
    {
        $updateHistory = confirm(
            label: 'Do you want to update history?',
            default: true
        );

        if ($updateHistory) {
            $responses = form()
                ->select(label: 'ID?', name: 'id', options: $histories->pluck('id')->toArray(), default: $histories->last()->id, required: true)
                ->text(label: 'Start Time?', name: 'start_time', default: $histories->last()->start_time, required: true)
                ->text(label: 'Elapsed Time (in hrs)?', name: 'elapsed_time', default: $histories->last()->elapsed_time / 3600, required: true)
                ->select(label: 'Is Running?', name: 'is_running', options: ['true', 'false'], default: $histories->last()->is_running, required: true)
                ->submit();

            TrackerHistory::where('id', $responses['id'])->update([
                'start_time' => $responses['start_time'],
                'is_running' =>  (bool)$responses['is_running'],
            ]);

            $history = TrackerHistory::where('id', $responses['id'])->first();
            TrackerHistory::where('id', $responses['id'])->update([
                'end_time' => \Carbon\Carbon::parse($responses['start_time'])->addHours($responses['elapsed_time']),
                'elapsed_time' =>  $responses['elapsed_time'] * 3600,
            ]);

            $histories = TrackerHistory::select('id', 'start_time', 'end_time', 'elapsed_time', 'is_running')->where('task_id', $taskId)->get();

            info('Updated Task Histories:');

            table(['ID', 'Start Time', 'End Time', 'Elapsed Time', 'Is Running'], $histories->toArray());

            $this->updateHistory($histories, $taskId);
        }
    }
}
