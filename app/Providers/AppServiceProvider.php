<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
		if (app()->environment('production')) {
        URL::forceScheme('https');
    }
        View::composer('*', function ($view) {
            $hiddenStatuses = [];
            $user = Auth::user();

            if ($user && $user->hasRole('Employee')) {
                $hiddenStatuses = ['closed', 'completed'];
            }
            if ($user) {
                $projectQuery = $user->hasRole('Admin') ? $user->projects() : $user->projectMembers();

                $projects = $projectQuery->withCount([
                    'tasks as to_do_tasks' => fn($query) => $query->where('status', 'to_do'),
                    'tasks as in_progress_tasks' => fn($query) => $query->where('status', 'in_progress'),
                    'tasks as in_review_tasks' => function ($query) {
                        $query->where('status', 'in_review');
                    },
                ])->with('users')->get();

                $view->with([
                    'hiddenStatuses' => $hiddenStatuses,
                    'projectsDropdown' => $projects,
                ]);
            }
        });

    }

}
