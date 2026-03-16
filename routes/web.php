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
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\HourController;

use App\Http\Controllers\TagController;

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::get('/users/resigned', [UserController::class, 'resignedUsers'])->name('resign.users.index');
    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::resource('users', UserController::class);

    Route::get('mail', [MailController::class, 'index'])->name('mail.inbox');

    Route::get('/master-hours', [HourController::class, 'create'])->name('master.hours.create');
    Route::post('/master-hours', [HourController::class, 'store'])->name('master.hours.store');
    Route::get('master/hours/get/{year}', [HourController::class, 'getHoursByYear'])->name('master.hours.get');

    Route::resource('clients', ProjectController::class);
    Route::get('/service-listing', [ProjectController::class, 'serviceListing'])->name('services.listing');
    Route::post('clients/{clientId}/restore', [ProjectController::class, 'restore'])->name('clients.restore');
    Route::post('client/team', [ProjectController::class, 'addMember'])->name('clients.addMember');
    Route::post('/clients/remove-member', [ProjectController::class, 'removeMember'])->name('clients.removeMember');
    Route::get('/clients/{clientId}/users', [ProjectController::class, 'getProjectUsers'])->name('clients.getUsers');

    Route::prefix('clients/{client}')->group(function () {
        Route::get('/services', [ProjectController::class, 'services'])->name('clients.services.index');
        Route::get('/services/create', [ProjectController::class, 'createService'])->name('clients.services.create');
        Route::post('/services', [ProjectController::class, 'storeService'])->name('clients.services.store');
        Route::get('/services/{service}', [ProjectController::class, 'servicesShow'])
            ->name('clients.services.show');
        Route::get('/services/{service}/edit', [ProjectController::class, 'editService'])->name('clients.services.edit');
        Route::put('/services/{service}', [ProjectController::class, 'updateService'])->name('clients.services.update');
        Route::delete('/services/{service}', [ProjectController::class, 'destroyService'])->name('clients.services.destroy');
    });

    Route::get('projects/{project}/tasks', [TaskController::class, 'index'])->name('clients.tasks.index');
    Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('clients.tasks.store');

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


    Route::resource('task.files', FileController::class);

    Route::resource('skills', SkillController::class);
    Route::resource('tags', TagController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/daily-report',[DailyReportController::class,'index'])->name('daily.report');

    Route::resource('checklist-items', ChecklistItemController::class);
    Route::get('checklist-items/{checklistItem}/update-status', [ChecklistItemController::class, 'updateStatus'])->name('checklist-items.update-status');

    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    Route::get('trackertime', [TaskController::class, 'trackerTimeAlertMail']);
});

Auth::routes();

Route::get('home', [HomeController::class, 'home'])->name('home');
