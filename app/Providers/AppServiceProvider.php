<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;

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
		
        Paginator::useBootstrapFive();
 
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\Blade::directive('progressColorClass', function ($expression) {
            return "<?php echo \App\Models\Project::progressColorClass($expression); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('progressColorHex', function ($expression) {
            return "<?php echo \App\Models\Project::progressColorHex($expression); ?>";
        });

        View::composer('*', function ($view) {
            $hiddenStatuses = [];
            $user = Auth::user();

            if ($user && $user->hasRole(\App\Support\Roles::RESOURCE)) {
                $hiddenStatuses = ['closed', 'completed'];
                // $hiddenStatuses = [];
            }
            if ($user) {
                $projectQuery = $user->can('clients-access') ? $user->projects() : $user->projectMembers();

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
