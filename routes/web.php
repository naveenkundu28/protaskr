<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ChecklistItemController;
use App\Http\Controllers\HomeController;

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::get('mail', [MailController::class, 'index'])->name('mail.inbox');

    Route::resource('projects', ProjectController::class);
    Route::post('projects/{projectId}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    Route::post('project/team', [ProjectController::class, 'addMember'])->name('projects.addMember');
    Route::post('/projects/remove-member', [ProjectController::class, 'removeMember'])->name('projects.removeMember');
    Route::get('/projects/{projectId}/users', [ProjectController::class, 'getProjectUsers'])->name('projects.getUsers');

    Route::get('projects/{project}/tasks', [TaskController::class, 'index'])->name('projects.tasks.index');
    Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');

    Route::get('tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('manual/{task}', [TaskController::class, 'mnanualTrack'])->name('tasks.manual');
    Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('task.updateStatus');
    Route::post('send-chat/{taskid}', [TaskController::class, 'sendChat'])->name('chat.send');
    Route::post('/task/{taskid}/start', [TaskController::class, 'startTimer'])->name('task.start');
    Route::post('/task/{taskid}/pause', [TaskController::class, 'pauseTimer'])->name('task.pause');
    Route::post('/task/{taskid}/stop', [TaskController::class, 'stopTimer'])->name('task.stop');
    Route::post('/task/{task}/save-time', [TaskController::class, 'saveTime'])->name('task.save-time');
    Route::post('/task/markcomplete/{projectId?}', [TaskController::class, 'markComplete'])->name('task.mark-complete');
    Route::post('/task/export', [TaskController::class, 'export'])->name('tasks.export');
   

    Route::resource('files', FileController::class);

    Route::resource('checklist-items', ChecklistItemController::class);
    Route::get('checklist-items/{checklistItem}/update-status', [ChecklistItemController::class, 'updateStatus'])->name('checklist-items.update-status');

    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    
    Route::get('trackertime', [TaskController::class, 'trackerTimeAlertMail']);

    
});

Auth::routes();

Route::get('home', [HomeController::class, 'home'])->name('home');
