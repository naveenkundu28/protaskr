<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        $this->registerPermissionGates();
    }

    /**
     * Define one gate per permission listed in config/permissions.php.
     * A user passes a gate only when they hold that permission (user_permissions).
     * The roles listed in the config are the defaults a new user starts with.
     */
    protected function registerPermissionGates(): void
    {
        foreach (array_keys(config('permissions', [])) as $permission) {
            if ($permission === 'projects-show') {
                Gate::define($permission, function (User $user, ?Project $project = null) use ($permission) {
                    if (!$user->hasPermission($permission)) {
                        return false;
                    }

                    if ($project && $user->hasRole(Roles::CLIENT)) {
                        return $user->client?->id == $project?->client_id;
                    }

                    if ($project && $user->hasRole(Roles::RESOURCE)) {
                        return $project->members()->whereKey($user->id)->exists();
                    }

                    return true;
                });

                continue;
            }

            if (in_array($permission, ['tasks-show', 'tasks-update', 'tasks-delete', 'tasks-track-manually'], true)) {
                Gate::define($permission, function (User $user, ?Task $task = null) use ($permission) {
                    if (!$user->hasPermission($permission)) {
                        return false;
                    }

                    if ($task && $user->hasRole(Roles::CLIENT)) {
                        return $user->client?->id == $task->client_id;
                    }

                    if ($task && $user->hasRole(Roles::RESOURCE)) {
                        if ($permission === 'tasks-show' && $task->project_id) {
                            return $task->deliveryProject()
                                ->whereHas('members', fn ($query) => $query->whereKey($user->id))
                                ->exists();
                        }

                        return $task->user_id == $user->id || $task->created_by == $user->id;
                    }

                    return true;
                });

                continue;
            }

            Gate::define($permission, function (User $user) use ($permission) {
                return $user->hasPermission($permission);
            });
        }
    }
}
