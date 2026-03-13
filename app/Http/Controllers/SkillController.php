<?php

namespace App\Http\Controllers;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkillController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('user-show');
        $skills = Skill::with([
            'primaryUsers:id,name',
            'secondaryUsers:id,name',
        ])->latest()->paginate(50);

        return view('skills.index', compact('skills'))->with('i', ($request->input('page', 1) - 1) * 5);
    }


    public function create()
    {
        $this->authorize('user-create');
        return view('skills.create');
    }


    public function store(Request $request) : RedirectResponse
    {
        $this->authorize('user-create');

        $request->validate([
            'name' => 'required|unique:skills,name'
        ]);

        Skill::create([
            'name' => $request->name
        ]);

        return redirect()->route('skills.index')
            ->with('success','Skill created successfully');
    }

    public function show($id): View
    {
        $this->authorize('user-show');

        $skills = Skill::findOrFail($id);

        return view('skills.show',compact('skills'));
    }

    public function edit($id): View
    {
        $this->authorize('user-edit');
        $skills = Skill::findOrFail($id);

        return view('skills.edit', compact('skills'));
    }


    public function update(Request $request, $id): RedirectResponse
    {
        $this->authorize('user-edit');

        $request->validate([
            'name' => 'required|unique:skills,name,'.$id
        ]);

        $skills = Skill::findOrFail($id);

        $skills->update([
            'name' => $request->name
        ]);

        return redirect()->route('skills.index')
            ->with('success','Skill updated successfully');
    }


    public function destroy($id)
    {
        $this->authorize('user-delete');
        $skills = Skill::findOrFail($id);

        $skills->delete();

        return redirect()->route('skills.index')
            ->with('success','Skill deleted successfully');
    }
}
