<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Task;
use Illuminate\Console\Command;
use App\Mail\TrackerTimeAlertMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TrackerTimeAlertMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:tracker-alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send tracker time alert emails for tasks exceeding 4 hours in the last 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $tasks = Task::whereHas('trackerHistories', function ($query) {
            $query->select(DB::raw('task_id, SUM(elapsed_time) as total_elapsed_time'))
                  ->whereBetween('start_time', [Carbon::now()->subDay(), Carbon::now()])
                  ->groupBy('task_id')
                  ->having('total_elapsed_time', '>', 14400); 
        })
        ->with(['project', 'user', 'trackerHistories']) 
        ->get()
        ->map(function ($task) {
            // Calculate total elapsed time across all tracker histories for the task
            $totalElapsedTime = $task->trackerHistories->sum('elapsed_time');
            // Convert total elapsed time to hours, minutes, and seconds
            $hours = floor($totalElapsedTime / 3600);
            $minutes = floor(($totalElapsedTime % 3600) / 60);
            $seconds = $totalElapsedTime % 60;
            $task->formatted_elapsed_time = sprintf('%02d hours, %02d mins, %02d secs', $hours, $minutes, $seconds);
            return $task;
        });


        if ($tasks->isNotEmpty()) {
            $html = '<h2>Task Report (Last 24 Hours, Time > 4 Hours)</h2>';
            $html .= '<p>Make sure to divide bigger tasks into parts and track not more than 2-3 hours per task.</p>';
            $html .= '<table border="1" cellpadding="10" cellspacing="0">';
            $html .= '<thead>
            <tr>
                <th>Task Name</th>
                <th>Project</th>
                <th>Due Date</th>
                <th>Assigned To</th>
                <th>Time Taken</th>
            </tr>
          </thead>';
            $html .= '<tbody>';
    
            $usersInCc = [];
    
            foreach ($tasks as $task) {
                $html .= "<tr>
                <td>{$task->title}</td>
                <td>{$task->project->name}</td>
                <td>{$task->due_date}</td>
                <td>{$task->user->name}</td>
                <td>{$task->formatted_elapsed_time}</td>
              </tr>";
    
                if (!in_array($task->user->email, $usersInCc)) {
                    $usersInCc[] = $task->user->email;
                }
            }
    
            $html .= '</tbody></table>';


            Mail::to(['anshulsehgal@virtualemployee.com','naveenkundu@virtualemployee.com','smuddin@virtualemployee.com'])
                ->send(new TrackerTimeAlertMail($html, $usersInCc));
        }
    }
}
