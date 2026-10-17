<?php

namespace App\Http\Controllers;

use App\Models\LeadStatus;
use Illuminate\Http\Request;

class LeadStatusController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('leads-status-access');
        $search = trim((string) $request->input('search'));
        $leadStatuses = LeadStatus::when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();
        return view('leads.statuses.index', compact('leadStatuses'));
    }

    // Show create form
    public function create()
    {
        $this->authorize('leads-status-create');
        return view('leads.statuses.create');
    }

    // Store
    public function store(Request $request)
    {
        $this->authorize('leads-status-create');
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        LeadStatus::create([
            'name' => $request->name
        ]);

        return redirect()->route('lead-statuses.index')
            ->with('success', 'Lead Status Created Successfully');
    }

    // Show edit form
    public function edit($id)
    {
        $this->authorize('leads-status-update');
        $leadStatus = LeadStatus::findOrFail($id);
        return view('leads.statuses.edit', compact('leadStatus'));
    }

    // Update
    public function update(Request $request, $id)
    {
        $this->authorize('leads-status-update');
        $leadStatus = LeadStatus::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $leadStatus->update([
            'name' => $request->name,
        ]);

        return redirect()->route('lead-statuses.index')
            ->with('success', 'Lead Status Updated Successfully');
    }

    // Delete
    public function destroy($id)
    {
        $this->authorize('leads-status-delete');
        $leadStatus = LeadStatus::findOrFail($id);
        $leadStatus->delete();

        return redirect()->route('lead-statuses.index')
            ->with('success', 'Lead Status Deleted Successfully');
    }
}
