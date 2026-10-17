<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClientProfileController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HourController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadStatusController;
use App\Http\Controllers\NotesHistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileBrowserController;
use App\Http\Controllers\ProjectModuleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Conventions
|--------------------------------------------------------------------------
| URLs:   plural module name, kebab-case, no leading slash.
| Names:  <module>.<action>, nested as <module>.<sub-module>.<action>.
| Params: named after the record they identify ({task}, {client}, {user}).
| Fixed paths (users/resigned) are declared before the {parameter} routes
| of the same module so they are not mistaken for an id.
*/

/* Authentication */
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    /* Dashboard and own profile */
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    /* Users */
    Route::get('users/resigned', [UserController::class, 'resignedUsers'])->name('users.resigned');
    Route::get('users/listing', [UserController::class, 'listing'])->name('users.listing');
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::get('users/{user}/permissions', [UserPermissionController::class, 'edit'])->name('users.permissions.edit');
    Route::put('users/{user}/permissions', [UserPermissionController::class, 'update'])->name('users.permissions.update');
    Route::resource('users', UserController::class);

    /* Projects: deliverables owned by a client */
    Route::get('projects', [ProjectModuleController::class, 'index'])->name('projects.index');
    Route::get('projects/create', [ProjectModuleController::class, 'create'])->name('projects.create');
    Route::post('projects', [ProjectModuleController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectModuleController::class, 'show'])->name('projects.show');
    Route::put('projects/{project}', [ProjectModuleController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}', [ProjectModuleController::class, 'destroy'])->name('projects.destroy');

    Route::post('projects/{project}/files', [ProjectFileBrowserController::class, 'upload'])->name('projects.files.store');
    Route::get('projects/{project}/files/{file}/download', [ProjectFileBrowserController::class, 'download'])->name('projects.files.download');
    Route::delete('projects/{project}/files/{file}', [ProjectFileBrowserController::class, 'destroyFile'])->name('projects.files.destroy');
    Route::post('projects/{project}/folders', [ProjectFileBrowserController::class, 'folder'])->name('projects.folders.store');
    Route::get('projects/{project}/folders/{folder}/download', [ProjectFileBrowserController::class, 'downloadFolder'])->name('projects.folders.download');
    Route::delete('projects/{project}/folders/{folder}', [ProjectFileBrowserController::class, 'destroyFolder'])->name('projects.folders.destroy');

    /* Stories */
    Route::get('stories/create', [StoryController::class, 'create'])->name('stories.create');
    Route::post('stories', [StoryController::class, 'store'])->name('stories.store');
    Route::get('stories/{story}', [StoryController::class, 'show'])->name('stories.show');
    Route::put('stories/{story}', [StoryController::class, 'update'])->name('stories.update');
    Route::patch('stories/{story}/status', [StoryController::class, 'updateStatus'])->name('stories.status.update');
    Route::delete('stories/{story}', [StoryController::class, 'destroy'])->name('stories.destroy');
    Route::post('stories/{story}/tasks', [StoryController::class, 'storeTask'])->name('stories.tasks.store');

    /* Clients */
    Route::post('clients/members', [ProjectController::class, 'addMember'])->name('clients.members.store');
    Route::post('clients/members/remove', [ProjectController::class, 'removeMember'])->name('clients.members.destroy');
    Route::post('clients/{client}/complete', [ProjectController::class, 'markCompleted'])->name('clients.complete');
    Route::post('clients/{client}/restore', [ProjectController::class, 'restore'])->name('clients.restore');
    Route::get('clients/{client}/users', [ProjectController::class, 'getProjectUsers'])->name('clients.users.index');
    Route::resource('clients', ProjectController::class);

    Route::get('clients/{client}/profile', [ClientProfileController::class, 'show'])->name('clients.profile.show');
    Route::post('clients/{client}/profile', [ClientProfileController::class, 'store'])->name('clients.profile.store');
    Route::put('clients/{client}/profile/password', [ClientProfileController::class, 'updatePassword'])->name('clients.profile.password');
    Route::delete('clients/{client}/profile', [ClientProfileController::class, 'destroy'])->name('clients.profile.destroy');

    Route::get('clients/{client}/services', [ProjectController::class, 'services'])->name('clients.services.index');
    Route::post('clients/{client}/services', [ProjectController::class, 'storeService'])->name('clients.services.store');
    Route::get('clients/{client}/services/{service}', [ProjectController::class, 'servicesShow'])->name('clients.services.show');
    Route::put('clients/{client}/services/{service}', [ProjectController::class, 'updateService'])->name('clients.services.update');
    Route::delete('clients/{client}/services/{service}', [ProjectController::class, 'destroyService'])->name('clients.services.destroy');

    /* Services across all clients */
    Route::get('services/listing', [ProjectController::class, 'serviceListing'])->name('services.listing.index');
    Route::post('services/listing/import', [ProjectController::class, 'importServices'])->name('services.listing.import');
    Route::get('services/bulk', [ProjectController::class, 'addServicesTable'])->name('services.bulk.edit');
    Route::post('services/bulk', [ProjectController::class, 'saveServicesTable'])->name('services.bulk.update');
    Route::post('services/bulk/copy-previous', [ProjectController::class, 'copyPreviousMonthServices'])->name('services.bulk.copy-previous');

    /* Tasks */
    Route::post('tasks/export', [TaskController::class, 'export'])->name('tasks.export');
    Route::post('tasks/bulk-complete', [TaskController::class, 'markComplete'])->name('tasks.bulk-complete');
    Route::match(['post', 'put'], 'tasks/time-entries/{trackerHistory}', [TaskController::class, 'updateTime'])->name('tasks.time-entries.update');

    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::post('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status.update');
    Route::post('tasks/{task}/chat', [TaskController::class, 'sendChat'])->name('tasks.chat.store');
    Route::put('tasks/{task}/manual-time', [TaskController::class, 'mnanualTrack'])->name('tasks.manual-time.update');
    Route::post('tasks/{task}/timer/start', [TaskController::class, 'startTimer'])->name('tasks.timer.start');
    Route::post('tasks/{task}/timer/stop', [TaskController::class, 'stopTimer'])->name('tasks.timer.stop');
    Route::post('tasks/{task}/timer/save', [TaskController::class, 'saveTime'])->name('tasks.timer.save');

    Route::get('tasks/{task}/files/{file}/download', [FileController::class, 'download'])->name('tasks.files.download');
    Route::resource('tasks.files', FileController::class);

    /* Leads */
    Route::resource('leads', LeadController::class)->except(['show']);
    Route::resource('leads.notes-histories', NotesHistoryController::class)
        ->parameters(['notes-histories' => 'notesHistory'])
        ->except(['show']);
    Route::resource('lead-statuses', LeadStatusController::class)->except(['show']);

    /* Masters */
    Route::resource('skills', SkillController::class);
    Route::resource('tags', TagController::class);
    Route::resource('requirements', RequirementController::class)->except(['show']);

    Route::get('hours', [HourController::class, 'create'])->name('hours.index');
    Route::post('hours', [HourController::class, 'store'])->name('hours.store');
    Route::get('hours/{year}', [HourController::class, 'getHoursByYear'])->name('hours.show');

    /* Reports */
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('reports/hourly', [DailyReportController::class, 'index'])->name('reports.hourly.index');
    Route::post('reports/hourly/update', [DailyReportController::class, 'update'])->name('reports.hourly.update');
    Route::post('reports/hourly/generate-weekly', [DailyReportController::class, 'generateWeekly'])->name('reports.hourly.generate-weekly');

    Route::get('reports/daily-task', [DailyReportController::class, 'getDailyTaskReports'])->name('reports.daily-task.index');
    Route::get('reports/daily-task/users', [DailyReportController::class, 'getUserTasks'])->name('reports.daily-task.users');
});

/* Password reset, registration and verification screens */
Auth::routes();

/* Where login redirects to */
Route::get('home', [HomeController::class, 'home'])->name('home');
