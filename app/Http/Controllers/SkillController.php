<?php

namespace App\Http\Controllers;
use App\Models\Service;
use App\Models\Skill;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkillController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('skills-access');
        $search = trim((string) $request->input('search'));
        $now = Carbon::now();

        $assignedUserIds = Service::query()
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->where('service_type', '!=', 'external')
            ->get(['front_resource', 'working_resource'])
            ->flatMap(function ($service) {
                return [$service->front_resource, ...$service->working_resource_ids];
            })->filter()->unique()->values();

        $skills = Skill::with([
            'primaryUsers:id,name,user_type',
            'secondaryUsers:id,name,user_type',
        ])->when($search !== '', function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%");
        })->latest()->paginate(50)->withQueryString();

        return view('skills.index', compact('skills', 'assignedUserIds'))->with('i', ($request->input('page', 1) - 1) * 5);
    }


    public function create()
    {
        $this->authorize('skills-create');
        return view('skills.create');
    }


    public function store(Request $request) : RedirectResponse
    {
        $this->authorize('skills-create');

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
        $this->authorize('skills-access');

        $skills = Skill::findOrFail($id);

        return view('skills.show',compact('skills'));
    }

    public function edit($id): View
    {
        $this->authorize('skills-update');
        $skills = Skill::findOrFail($id);

        return view('skills.edit', compact('skills'));
    }


    public function update(Request $request, $id): RedirectResponse
    {
        $this->authorize('skills-update');

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
        $this->authorize('skills-delete');
        $skills = Skill::findOrFail($id);

        $skills->delete();

        return redirect()->route('skills.index')
            ->with('success','Skill deleted successfully');
    }
}
