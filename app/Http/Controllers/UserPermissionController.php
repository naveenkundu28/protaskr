<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserPermissionController extends Controller
{
    /**
     * An admin cannot take this permission away from themselves, so they
     * cannot lock themselves out of this page.
     */
    private const SELF_PROTECTED = 'users-permissions';

    public function edit(User $user): View
    {
        $this->authorize('users-permissions');

        // group the permission names by module: "leads-notes-create" belongs to "leads"
        $groups = [];
        foreach (array_keys(config('permissions', [])) as $permission) {
            $groups[strtok($permission, '-')][] = $permission;
        }

        return view('users.permissions', [
            'user' => $user,
            'groups' => $groups,
            'granted' => $user->permissionNames(),
            'defaults' => Roles::defaultPermissions($user->role?->name),
            'selfProtected' => $user->is(auth()->user()) ? self::SELF_PROTECTED : null,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('users-permissions');

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $permissions = $request->boolean('reset')
            ? Roles::defaultPermissions($user->role?->name)
            : $request->input('permissions', []);

        if ($user->is(auth()->user())) {
            $permissions[] = self::SELF_PROTECTED;
        }

        $user->syncPermissions($permissions);

        return redirect()->route('users.permissions.edit', $user)
            ->with('success', $request->boolean('reset')
                ? 'Permissions reset to the ' . ($user->role?->name ?? 'role') . ' defaults.'
                : 'Permissions updated successfully.');
    }
}
