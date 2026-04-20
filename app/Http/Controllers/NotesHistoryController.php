<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\NotesHistory;
use Illuminate\Http\Request;

class NotesHistoryController extends Controller
{
    public function index(Request $request, Lead $lead)
    {
        $this->authorize('user-show');

        $search = trim((string) $request->input('search'));

        $notesHistories = $lead->notesHistories()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('notes', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('actions', 'like', "%{$search}%");
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

        return view('notes_histories.create', compact('lead'));
    }

    public function store(Request $request, Lead $lead)
    {
        $this->authorize('user-create');

        $request->validate([
            'notes' => 'required|string',
            'status' => 'required|in:email_inputs_shared,in_call_discussion',
            'actions' => 'nullable|string|max:255',
        ]);

        $lead->notesHistories()->create($request->only('notes', 'status', 'actions'));

        return redirect()->route('leads.notes-histories.index', $lead->id)
            ->with('success', 'Notes history created successfully');
    }

    public function edit(Lead $lead, NotesHistory $notesHistory)
    {
        $this->authorize('user-edit');
        $this->ensureNoteBelongsToLead($lead, $notesHistory);

        return view('notes_histories.edit', compact('lead', 'notesHistory'));
    }

    public function update(Request $request, Lead $lead, NotesHistory $notesHistory)
    {
        $this->authorize('user-edit');
        $this->ensureNoteBelongsToLead($lead, $notesHistory);

        $request->validate([
            'notes' => 'required|string',
            'status' => 'required|in:email_inputs_shared,in_call_discussion',
            'actions' => 'nullable|string|max:255',
        ]);

        $notesHistory->update($request->only('notes', 'status', 'actions'));

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
