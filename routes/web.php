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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\HourController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadStatusController;
use App\Http\Controllers\NotesHistoryController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ProjectModuleController;
use App\Http\Controllers\StoryController;

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    /* Project management module.  These routes intentionally sit apart from the
       legacy client routes below: clients remain the CRM entity while projects
       are now deliverables owned by a client. */
    Route::get('projects', [ProjectModuleController::class, 'index'])->name('projects.index');
    Route::get('projects/create', [ProjectModuleController::class, 'create'])->name('projects.create');
    Route::post('projects', [ProjectModuleController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectModuleController::class, 'show'])->name('projects.show');
    Route::get('projects/{project}/edit', [ProjectModuleController::class, 'edit'])->name('projects.edit');
    Route::put('projects/{project}', [ProjectModuleController::class, 'update'])->name('projects.update');
    Route::get('projects/{project}/stories', [ProjectModuleController::class, 'stories'])->name('projects.stories');
    Route::get('stories/create', [StoryController::class, 'create'])->name('stories.create');
    Route::post('stories', [StoryController::class, 'store'])->name('stories.store');
    Route::get('stories/{story}', [StoryController::class, 'show'])->name('stories.show');
    Route::post('stories/{story}/tasks', [StoryController::class, 'storeTask'])->name('stories.tasks.store');
    Route::resource('roles', RoleController::class);
    Route::get('/users/resigned', [UserController::class, 'resignedUsers'])->name('resign.users.index');
    Route::get('/users/listing', [UserController::class, 'listing'])->name('users.listing');
    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::resource('users', UserController::class);
    
    Route::middleware(['leads_access'])->group(function () {
        Route::resource('leads', LeadController::class);
        Route::resource('leads.notes-histories', NotesHistoryController::class)
            ->parameters(['notes-histories' => 'notesHistory'])
            ->except(['show']);
    });

    Route::get('mail', [MailController::class, 'index'])->name('mail.inbox');

    Route::get('/master-hours', [HourController::class, 'create'])->name('master.hours.create');
    Route::post('/master-hours', [HourController::class, 'store'])->name('master.hours.store');
    Route::get('master/hours/get/{year}', [HourController::class, 'getHoursByYear'])->name('master.hours.get');

    Route::get('/requirements', [RequirementController::class, 'index'])->name('requirements.index');
    Route::get('/requirements/create', [RequirementController::class, 'create'])->name('requirements.create');
    Route::post('/requirements', [RequirementController::class, 'store'])->name('requirements.store');
    Route::get('/requirements/{id}/edit', [RequirementController::class, 'edit'])->name('requirements.edit');
    Route::put('/requirements/{id}', [RequirementController::class, 'update'])->name('requirements.update');
    Route::delete('/requirements/{id}', [RequirementController::class, 'destroy'])->name('requirements.destroy');

    Route::get('/lead-statuses', [LeadStatusController::class, 'index'])->name('lead-statuses.index');
    Route::get('/lead-statuses/create', [LeadStatusController::class, 'create'])->name('lead-statuses.create');
    Route::post('/lead-statuses', [LeadStatusController::class, 'store'])->name('lead-statuses.store');
    Route::get('/lead-statuses/{id}/edit', [LeadStatusController::class, 'edit'])->name('lead-statuses.edit');
    Route::put('/lead-statuses/{id}', [LeadStatusController::class, 'update'])->name('lead-statuses.update');
    Route::delete('/lead-statuses/{id}', [LeadStatusController::class, 'destroy'])->name('lead-statuses.destroy');

    Route::resource('clients', ProjectController::class);
    Route::post('clients/{client}/complete', [ProjectController::class, 'markCompleted'])->name('clients.complete');
    Route::get('/service-listing', [ProjectController::class, 'serviceListing'])->name('services.listing');
    Route::post('/service-listing/import', [ProjectController::class, 'importServices'])->name('services.import');
    Route::get('/services/add', [ProjectController::class, 'addServicesTable'])->name('services.add');
    Route::post('/services/add', [ProjectController::class, 'saveServicesTable'])->name('services.save');
    Route::post('/services/copy-previous', [ProjectController::class, 'copyPreviousMonthServices'])->name('services.copyPrevious');
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

    Route::match(['POST', 'PUT'] ,'tasks/{trackerId}/update-time', [TaskController::class, 'updateTime'])->name('tasks.updateTime');

    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('task.updateStatus');
    Route::post('send-chat/{taskid}', [TaskController::class, 'sendChat'])->name('chat.send');
    Route::post('/task/{taskid}/start', [TaskController::class, 'startTimer'])->name('task.start');
    Route::post('/task/{taskid}/pause', [TaskController::class, 'pauseTimer'])->name('task.pause');
    Route::post('/task/{taskid}/stop', [TaskController::class, 'stopTimer'])->name('task.stop');
    Route::post('/task/{task}/save-time', [TaskController::class, 'saveTime'])->name('task.save-time');
    Route::post('/task/markcomplete/{projectId?}', [TaskController::class, 'markComplete'])->name('task.mark-complete');
    Route::post('/task/export', [TaskController::class, 'export'])->name('tasks.export');


    Route::get('task/{task}/files/{file}/download', [FileController::class, 'download'])->name('task.files.download');
    Route::resource('task.files', FileController::class);

    Route::resource('skills', SkillController::class);
    Route::resource('tags', TagController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/hourly-report', [DailyReportController::class, 'index'])->name('daily.report');
    Route::post('/hourly-report/update', [DailyReportController::class, 'update'])->name('daily.report.update');
    Route::post('/hourly-report/generate-weekly', [DailyReportController::class, 'generateWeekly'])->name('daily.report.generate-weekly');
    Route::get('/daily-task-report', [DailyReportController::class, 'getDailyTaskReports'])->name('daily.task.report');
    Route::get('/daily-task-report/users', [DailyReportController::class, 'getUserTasks'])->name('daily.task.report.users');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    Route::resource('checklist-items', ChecklistItemController::class);
    Route::get('checklist-items/{checklistItem}/update-status', [ChecklistItemController::class, 'updateStatus'])->name('checklist-items.update-status');

    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    Route::get('trackertime', [TaskController::class, 'trackerTimeAlertMail']);
});

Auth::routes();

Route::get('home', [HomeController::class, 'home'])->name('home');
