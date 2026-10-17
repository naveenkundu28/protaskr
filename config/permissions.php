<?php

use App\Support\Roles;

/*
|--------------------------------------------------------------------------
| Permissions
|--------------------------------------------------------------------------
|
| Every permission in the app, and the roles that are allowed to use it.
| A gate is defined for each key in AuthServiceProvider, so these names are
| what @can(...) and $this->authorize(...) check.
|
| Only the roles listed for a permission are allowed, Admin included: remove
| Admin from an array to hide that feature from Admin as well. A permission
| that is not listed here is denied for everyone.
|
*/

return [

    'users-access' => [Roles::ADMIN],
    'users-show' => [Roles::ADMIN],
    'users-create' => [Roles::ADMIN],
    'users-update' => [Roles::ADMIN],
    'users-permissions' => [Roles::ADMIN],
    'users-resign' => [Roles::ADMIN],
    'users-resign-restore' => [Roles::ADMIN],

    'projects-access' => [Roles::ADMIN, Roles::RESOURCE, Roles::CLIENT],
    'projects-show' => [Roles::ADMIN, Roles::RESOURCE, Roles::CLIENT],
    'projects-create' => [Roles::ADMIN],
    'projects-update' => [Roles::ADMIN],
    'projects-delete' => [Roles::ADMIN],
    'projects-settings' => [Roles::ADMIN],
    'projects-stories-create' => [Roles::ADMIN, Roles::RESOURCE, Roles::CLIENT],
    'projects-stories-update' => [Roles::ADMIN, Roles::RESOURCE, Roles::CLIENT],
    'projects-stories-delete' => [Roles::ADMIN, Roles::CLIENT],

    'clients-access' => [Roles::ADMIN],
    'clients-show' => [Roles::ADMIN],
    'clients-create' => [Roles::ADMIN],
    'clients-update' => [Roles::ADMIN],
    'clients-delete' => [Roles::ADMIN],
    'clients-services' => [Roles::ADMIN],
    'clients-profile-create' => [Roles::ADMIN],
    'clients-profile-update' => [Roles::ADMIN],
    'clients-profile-delete' => [Roles::ADMIN],

    'services-listing' => [Roles::ADMIN],
    'services-listing-import' => [Roles::ADMIN],
    'services-bulk-access' => [Roles::ADMIN],
    'services-bulk-update' => [Roles::ADMIN],

    'leads-access' => [Roles::ADMIN],
    'leads-create' => [Roles::ADMIN],
    'leads-update' => [Roles::ADMIN],
    'leads-delete' => [Roles::ADMIN],
    'leads-notes-access' => [Roles::ADMIN],
    'leads-notes-create' => [Roles::ADMIN],
    'leads-notes-update' => [Roles::ADMIN],
    'leads-notes-delete' => [Roles::ADMIN],

    'leads-status-access' => [Roles::ADMIN],
    'leads-status-create' => [Roles::ADMIN],
    'leads-status-update' => [Roles::ADMIN],
    'leads-status-delete' => [Roles::ADMIN],

    'tasks-access' => [Roles::ADMIN],
    'tasks-show' => [Roles::ADMIN, Roles::RESOURCE, Roles::CLIENT],
    'tasks-create' => [Roles::ADMIN, Roles::RESOURCE, Roles::CLIENT],
    'tasks-import' => [Roles::ADMIN],
    'tasks-export' => [Roles::ADMIN],
    'tasks-update' => [Roles::ADMIN, Roles::RESOURCE, Roles::CLIENT],
    'tasks-delete' => [Roles::ADMIN, Roles::RESOURCE, Roles::CLIENT],
    'tasks-bulk-complete' => [Roles::ADMIN],
    'tasks-time-update' => [Roles::ADMIN],
    'tasks-track-manually' => [Roles::ADMIN],

    'reports-access' => [Roles::ADMIN],
    'reports-hourly' => [Roles::ADMIN],
    'reports-hourly-update' => [Roles::ADMIN],
    'reports-daily-task' => [Roles::ADMIN],

    'skills-access' => [Roles::ADMIN],
    'skills-create' => [Roles::ADMIN],
    'skills-update' => [Roles::ADMIN],
    'skills-delete' => [Roles::ADMIN],

    'tags-access' => [Roles::ADMIN],
    'tags-create' => [Roles::ADMIN],
    'tags-update' => [Roles::ADMIN],
    'tags-delete' => [Roles::ADMIN],

    'hours-access' => [Roles::ADMIN],
    'hours-update' => [Roles::ADMIN],

    'requirements-access' => [Roles::ADMIN],
    'requirements-create' => [Roles::ADMIN],
    'requirements-update' => [Roles::ADMIN],
    'requirements-delete' => [Roles::ADMIN],

    'service-show' => [Roles::ADMIN],
    'service-create' => [Roles::ADMIN],
    'service-update' => [Roles::ADMIN],
    'service-delete' => [Roles::ADMIN],

];
