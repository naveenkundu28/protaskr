<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\Tag;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
    {
        $this->authorize('user-show');

        $data = User::with(['primarySkills', 'secondarySkills', 'tags'])->latest()->paginate(50);

        return view('users.index', compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        $this->authorize('user-create');

        $roles = Role::pluck('name', 'name')->all();
        $skills = Skill::all();
        $tags = Tag::all();

        return view('users.create', compact('roles', 'skills', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('user-create');

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'employee_id' => 'required|unique:users,employee_id',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->only([
            'name',
            'email',
            'password',
            'user_type',
            'employee_id',
            'joining_date',
            'phone_no',
            'alt_phone_no',
            'experience'
        ]);

        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        $user->assignRole($request->input('roles'));

        if ($request->has('primary_skills')) {
            $user->primarySkills()->sync($request->primary_skills);
        }

        if ($request->has('secondary_skills')) {
            $user->secondarySkills()->sync($request->secondary_skills);
        }

        if ($request->has('tags')) {
            $user->tags()->sync($request->tags);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $this->authorize('user-show');

        $user = User::with(['primarySkills', 'secondarySkills', 'tags'])->withTrashed()->findOrFail($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $this->authorize('user-edit');

        $user = User::with(['primarySkills', 'secondarySkills', 'tags'])->findOrFail($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        $skills = Skill::all();
        $tags = Tag::all();

        $userPrimarySkills = $user->primarySkills->pluck('id')->toArray();
        $userSecondarySkills = $user->secondarySkills->pluck('id')->toArray();
        $userTags = $user->tags->pluck('id')->toArray();

        return view('users.edit', compact('user', 'roles', 'userRole', 'skills', 'tags', 'userPrimarySkills', 'userSecondarySkills', 'userTags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->authorize('user-edit');

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'employee_id' => 'required|unique:users,employee_id',
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, array('password'));
        }

        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();

        $user->assignRole($request->input('roles'));

        $user->primarySkills()->sync($request->primary_skills ?? []);

        $user->secondarySkills()->sync($request->secondary_skills ?? []);

        $user->tags()->sync($request->tags ?? []);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $this->authorize('user-delete');
        $request->validate([
            'comment' => 'required|string'
        ]);
        $user = User::findOrFail($id);

        $user->comment = $request->comment;
        $user->save();
        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'User resigned successfully');
    }

    public function resignedUsers(Request $request): View
    {
        $this->authorize('user-show');

        $data = User::with(['primarySkills', 'secondarySkills', 'tags'])->onlyTrashed()->latest()->paginate(50);

        return view('users.resigned.index', compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }
}
