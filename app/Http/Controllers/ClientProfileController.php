<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * A client's "profile" is its login: a users row with the Client role,
 * linked through clients.profile_user_id.
 */
class ClientProfileController extends Controller
{
    public function show(Client $client): View|RedirectResponse
    {
        abort_unless(Gate::any(['clients-profile-create', 'clients-profile-update', 'clients-profile-delete']), 403);

        if (!$client->profile) {
            return redirect()->route('clients.index')
                ->with('error', 'No profile has been created for ' . $client->name . ' yet.');
        }

        return view('clients.profile', [
            'client' => $client,
            'profile' => $client->profile,
        ]);
    }

    public function store(Client $client): RedirectResponse
    {
        $this->authorize('clients-profile-create');

        if ($client->profile) {
            return redirect()->route('clients.profile.show', $client);
        }

        if (!filter_var($client->client_email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'Add a valid client email to ' . $client->name . ' before creating a profile.');
        }

        $existing = User::withTrashed()->where('email', $client->client_email)->first();

        if ($existing) {
            return back()->with('error', 'The email ' . $client->client_email . ' is already used by another user.');
        }

        $password = Str::password(12, symbols: false);

        $user = new User([
            'name' => $client->name,
            'email' => $client->client_email,
            'password' => Hash::make($password),
            'user_type' => 'Client',
            'experience' => 0,
            'is_show_report' => false,
        ]);
        $user->role()->associate(Role::where('name', Roles::CLIENT)->firstOrFail());
        $user->save();
        $user->assignDefaultPermissions();

        $client->profile()->associate($user)->save();

        return redirect()->route('clients.profile.show', $client)
            ->with('success', 'Client profile created.')
            ->with('generated_password', $password);
    }

    public function updatePassword(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('clients-profile-update');

        abort_unless($client->profile, 404);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $client->profile->update(['password' => Hash::make($request->password)]);

        return redirect()->route('clients.profile.show', $client)
            ->with('success', 'Password updated.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('clients-profile-delete');

        abort_unless($client->profile, 404);

        try {
            // Removes the users row only. clients.profile_user_id is set to
            // null by the foreign key; the client itself is untouched.
            $client->profile->forceDelete();
        } catch (QueryException $e) {
            return back()->with('error', 'This profile could not be deleted because other records still refer to it.');
        }

        return redirect()->route('clients.index')
            ->with('success', 'Profile for ' . $client->name . ' deleted.');
    }
}
