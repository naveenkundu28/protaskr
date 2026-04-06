<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit() : View
    {
        $user = auth()->user();

        // dd($user);

        $skills = Skill::all();

        $userPrimarySkills = $user->primarySkills->pluck('id')->toArray();
        $userSecondarySkills = $user->secondarySkills->pluck('id')->toArray();

        return view('profile.edit', compact('user', 'skills', 'userPrimarySkills', 'userSecondarySkills'));
    }

    public function update(Request $request) : RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'employee_id' => 'required|string|max:50|unique:users,employee_id,' . $user->id,
            'password' => 'same:confirm-password',
        ]);

        $data = $request->only([
            'name',
            'email',
            'employee_id',
            'joining_date',
            'experience',
            'phone_no',
            'alt_phone_no'
        ]);

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->update($data);
        
        $user->primarySkills()->sync($request->primary_skills ?? []);
        $user->secondarySkills()->sync($request->secondary_skills ?? []);

        return redirect()->route('dashboard')
            ->with('success', 'Profile updated successfully');
    }
}
