<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
            'shift_start_time' => 'nullable|date_format:H:i',
            'shift_end_time' => 'nullable|date_format:H:i',
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

        $hasStart = $request->filled('shift_start_time');
        $hasEnd = $request->filled('shift_end_time');

        if ($hasStart !== $hasEnd) {
            throw ValidationException::withMessages([
                'shift_start_time' => 'Choose both shift times, or leave both fields empty.',
            ]);
        }

        if ($hasStart && $hasEnd) {
            $start = Carbon::createFromFormat('H:i', $request->input('shift_start_time'));
            $end = Carbon::createFromFormat('H:i', $request->input('shift_end_time'));

            if ($start->greaterThanOrEqualTo($end)) {
                throw ValidationException::withMessages([
                    'shift_end_time' => 'Shift end must be later than shift start.',
                ]);
            }

            $data['shift_start_time'] = $start->format('H:i:s');
            $data['shift_end_time'] = $end->format('H:i:s');
        } else {
            $data['shift_start_time'] = null;
            $data['shift_end_time'] = null;
        }

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
