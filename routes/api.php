<?php

use App\Http\Controllers\Api\TrackerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('tracker/report', [TrackerController::class, 'report']);
Route::get('add-task', function(){
	if(request('submit')){
		$tasks = request('task');
		$tasks = array_filter($tasks ?? [], function($item) {
			return isset($item['title']) && trim($item['title']) !== '';
		});

        $duration = request('duration');

        // Generate N random decimal numbers that sum to $duration, where N = count($tasks)
        $taskCount = count($tasks);
        $breaks = [0];
        for ($i = 0; $i < $taskCount - 1; $i++) {
            $breaks[] = mt_rand(1, $duration * 100 - 1) / 100;
        }
        $breaks[] = $duration;
        sort($breaks);
        $randomParts = [];
        for ($i = 1; $i < count($breaks); $i++) {
            $randomParts[] = round($breaks[$i] - $breaks[$i - 1], 2);
        }

        $tasks = array_values($tasks); // reindex in case of gaps
        foreach ($tasks as $key => $task) {
            $taskDuration = $randomParts[$key] ?? 0;
            $createdAt = \Carbon\Carbon::parse(request('task_date'))->startOfDay()->addHours(12);
            if($key > 0){
                $createdAt = $createdAt->addHours($randomParts[$key-1]);
            }

            $taskModel = \App\Models\Task::create([
                'user_id' => 22,
                'client_id' => request('project_id'),
                'title' => $task['title'] ?? '',
                'description' => $task['description'] ?? '',
                'due_date' => request('task_date'),
                'priority' => 'low',
                'status' => 'in_review',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'start_time' => $createdAt,
                'end_time' => $createdAt->copy()->addHours($taskDuration*60),
                'elapsed_time' => $taskDuration*60*60,
                'is_running' => false,
            ]);

            \App\Models\TrackerHistory::create([
                'user_id' => $taskModel->user_id,
                'task_id' => $taskModel->id,
                'start_time' => $taskModel->start_time,
                'end_time' => $taskModel->end_time,
                'elapsed_time' => $taskModel->elapsed_time,
                'is_running' => $taskModel->is_running,
                'created_at' => $taskModel->created_at,
                'updated_at' => $taskModel->updated_at,
            ]);
        }

        return redirect('/api/add-task?success=Tasks added successfully for: '.request('task_date'));
	}

    if(request('success')){
        echo '<h4 style="color: green;">'.request('success').'</h4>';
    }

	$html = '<form>';
	for($i=0; $i<=5; $i++){
		$html .= '<input type="text" name="task['.$i.'][title]" placeholder="title '.$i.'" style="width: 100%; max-width: 500px; margin-bottom: 0.25rem;" /> <br />';
		$html .= '<textarea rows="1" type="text" name="task['.$i.'][description]" placeholder="description '.$i.'" style="width: 100%; max-width: 500px"></textarea><br />';
		$html .= '<br/>';
	}

	$html .= '<label>Total Duration:</label>';
	$html .= '<input type="number" name="duration" value="8" step="any" required>';
	$html .= '<br/><br/>';
    $html .= '<label>Date:</label>';
	$html .= '<input type="date" name="task_date" required>';
	$html .= '<br/><br/>';
    $html .= '<label>Project ID:</label>';
	$html .= '<input type="number" name="project_id" required>';
	$html .= '<br/><br/>';
	$html .= '<input type="submit" name="submit" value="submit">';
	$html .= '</form>';

	return $html;
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
