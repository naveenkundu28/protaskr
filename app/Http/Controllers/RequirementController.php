<?php

namespace App\Http\Controllers;

use App\Models\Requirement;
use Illuminate\Http\Request;

class RequirementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('user-show');
        $search = trim((string) $request->input('search'));
        $requirements = Requirement::when($search !== '', function ($query) use ($search) {
                $query->where('requirement', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();
        return view('requirement.index', compact('requirements'));
    }

    // Show create form
    public function create()
    {
        $this->authorize('user-create');
        return view('requirement.create');
    }


    // Store
    public function store(Request $request)
    {
        $this->authorize('user-create');
        $request->validate([
            'requirement' => 'required|string|max:255'
        ]);

        $requirement = Requirement::create([
            'requirement' => strtolower(str_replace(' ', '_', $request->requirement))
        ]);

        return redirect()->route('requirements.index')
            ->with('success', 'Requirement created successfully');
    }

    // Show single
    // public function show($id)
    // {
    //     $requirement = Requirement::findOrFail($id);
    //     return response()->json($requirement);
    // }

    // Show edit form
    public function edit($id)
    {
        $this->authorize('user-edit');
        $requirement = Requirement::findOrFail($id);
        return view('requirement.edit', compact('requirement'));
    }

    // Update
    public function update(Request $request, $id)
    {
        $this->authorize('user-edit');
        $requirement = Requirement::findOrFail($id);

        $request->validate([
            'requirement' => 'required|string|max:255'
        ]);

        $requirement->update([
            'requirement' => strtolower(str_replace(' ', '_', $request->requirement))
        ]);

        return redirect()->route('requirements.index')
            ->with('success', 'Requirement updated successfully');
    }

    // Delete
    public function destroy($id)
    {
        $this->authorize('user-delete');
        $requirement = Requirement::findOrFail($id);
        $requirement->delete();

        return redirect()->route('requirements.index')
            ->with('success', 'Requirement deleted successfully');
    }
}
