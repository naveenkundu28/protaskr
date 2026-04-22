<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\NotesHistory;
use Illuminate\Http\Request;
use App\Models\LeadStatus;

class NotesHistoryController extends Controller
{
    public function index(Request $request, Lead $lead)
    {
        $this->authorize('user-show');

        $search = trim((string) $request->input('search'));

        $notesHistories = $lead->notesHistories()->with('leadStatus')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('notes', 'like', "%{$search}%")

                        ->orWhere('actions', 'like', "%{$search}%")
                        ->orWhereHas('leadStatus', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('notes_histories.index', compact('lead', 'notesHistories'))
            ->with('i', ($request->input('page', 1) - 1) * 50);
    }

    public function create(Lead $lead)
    {
        $this->authorize('user-create');
        $leadStatuses = LeadStatus::all();
        return view('notes_histories.create', compact('lead', 'leadStatuses'));
    }

    public function store(Request $request, Lead $lead)
    {
        $this->authorize('user-create');

        $request->validate([
            'notes' => 'required|string',
            'lead_status_id' => 'required|exists:lead_statuses,id',
            'actions' => 'nullable|string|max:255',
        ]);

        $lead->notesHistories()->create([
            'notes' => $request->notes,
            'lead_status_id' => $request->lead_status_id,
            'actions' => $request->actions,
        ]);

        return redirect()->route('leads.notes-histories.index', $lead->id)
            ->with('success', 'Notes history created successfully');
    }

    public function edit(Lead $lead, NotesHistory $notesHistory)
    {
        $this->authorize('user-edit');
        $this->ensureNoteBelongsToLead($lead, $notesHistory);

        $leadStatuses = LeadStatus::all();

        return view('notes_histories.edit', compact('lead', 'notesHistory', 'leadStatuses'));
    }

    public function update(Request $request, Lead $lead, NotesHistory $notesHistory)
    {
        $this->authorize('user-edit');
        $this->ensureNoteBelongsToLead($lead, $notesHistory);

        $request->validate([
            'notes' => 'required|string',
            'lead_status_id' => 'required|exists:lead_statuses,id',
            'actions' => 'nullable|string|max:255',
        ]);

        $notesHistory->update([
            'notes' => $request->notes,
            'lead_status_id' => $request->lead_status_id,
            'actions' => $request->actions,
        ]);

        return redirect()->route('leads.notes-histories.index', $lead->id)
            ->with('success', 'Notes history updated successfully');
    }

    public function destroy(Lead $lead, NotesHistory $notesHistory)
    {
        $this->authorize('user-delete');
        $this->ensureNoteBelongsToLead($lead, $notesHistory);

        $notesHistory->delete();

        return redirect()->route('leads.notes-histories.index', $lead->id)
            ->with('success', 'Notes history deleted successfully');
    }

    private function ensureNoteBelongsToLead(Lead $lead, NotesHistory $notesHistory): void
    {
        abort_if($notesHistory->lead_id !== $lead->id, 404);
    }
}
