<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\NotesHistory;
use App\Models\Requirement;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('user-show');

        $search = trim((string) $request->input('search'));

        $leads = Lead::with(['requirement', 'tlUser', 'teamUser', 'latestNotesHistory'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('lead', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('leads.index', compact('leads'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }
    public function create()
    {
        $this->authorize('user-create');
        $requirements = Requirement::all();
        $skills = Skill::all();

        // Only users who can be TL (example filter)
        $users = User::where('show_leads', true)->get();

        return view('leads.create', compact('requirements', 'users', 'skills'));
    }

    public function store(Request $request)
    {
        $this->authorize('user-create');
        $request->validate([
            'lead'      => 'required|string|max:255',
            'status'         => 'required',
            'tl'             => 'required|exists:users,id',
            'team'           => 'required|exists:users,id',
            'requirement_id' => 'required|exists:requirements,id',
            'skills'         => 'nullable|array',
            'skills.*'       => 'required|string|max:255',
            'claimed_group'  => 'required|in:chat,gl,email',
            'call_done'      => 'required|boolean',
            'date'           => 'nullable|date',
            'jd'             => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // 1. Create Lead
            $lead = Lead::create([
                'lead'      => $request->lead,
                'tl'             => $request->tl,
                'team'           => $request->team,
                'requirement_id' => $request->requirement_id,
                'claimed_group'  => $request->claimed_group,
                'call_done'      => $request->call_done,
                'cam_member'     => $request->cam_member,
                'date'           => $request->date,
                'jd'             => $request->jd,
            ]);

            $lead->skills()->sync($this->resolveSkillIds($request->input('skills', [])));

            // 2. Insert Notes History
            if ($request->notes || $request->status) {
                NotesHistory::create([
                    'lead_id' => $lead->id,
                    'notes'   => $request->notes ?? 'Status updated',
                    'status'  => $request->status,
                    'actions' => null
                ]);
            }

            DB::commit();

            return redirect()->route('leads.index')
                ->with('success', 'Lead created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    protected function resolveSkillIds(array $skills): array
    {
        return collect($skills)
            ->map(function ($skill) {
                $skill = trim((string) $skill);
                if ($skill === '') {
                    return null;
                }

                if (ctype_digit($skill)) {
                    return Skill::where('id', $skill)->exists() ? (int)$skill : null;
                }

                $existing = Skill::whereRaw('LOWER(name) = ?', [mb_strtolower($skill)])->first();
                if ($existing) {
                    return $existing->id;
                }

                return Skill::create(['name' => $skill])->id;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    public function edit($id)
    {
        $this->authorize('user-edit');
        $lead = Lead::with('notesHistories')->findOrFail($id);
        $latestNote = $lead->notesHistories()->latest()->first();

        $users = User::where('show_leads', true)->get();
        $requirements = Requirement::all();

        // Latest status from notes_history
        $latestStatus = optional(
            $lead->notesHistories()->latest()->first()
        )->status;

        $skills = Skill::all();

        return view('leads.edit', compact(
            'lead',
            'users',
            'requirements',
            'latestStatus',
            'latestNote',
            'skills'
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('user-edit');
        $lead = Lead::findOrFail($id);

        $request->validate([
            'lead'      => 'required|string|max:255',
            'status'         => 'required|in:email_inputs_shared,in_call_discussion',
            'tl'             => 'required|exists:users,id',
            'team'           => 'required|exists:users,id',
            'requirement_id' => 'required|exists:requirements,id',
            'skills'         => 'nullable|array',
            'skills.*'       => 'required|string|max:255',
            'claimed_group'  => 'nullable|in:chat,gl,email',
            'call_done'      => 'required|boolean',
            'date'           => 'nullable|date',
            'jd'             => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // 1. Update Lead
            $lead->update([
                'lead'      => $request->lead,
                'tl'             => $request->tl,
                'team'           => $request->team,
                'requirement_id' => $request->requirement_id,
                'claimed_group'  => $request->claimed_group,
                'call_done'      => $request->call_done,
                'cam_member'     => $request->cam_member,
                'date'           => $request->date,
                'jd'             => $request->jd,
            ]);

            $lead->skills()->sync($this->resolveSkillIds($request->input('skills', [])));

            // 2. ALWAYS insert new note (history tracking)
            // NotesHistory::update([
            //     'lead_id' => $lead->id,
            //     'notes'   => $request->notes,
            //     'status'  => $request->status,
            //     'actions' => null
            // ]);
            if ($request->filled('notes') || $request->filled('status')) {
                NotesHistory::create([
                    'lead_id' => $lead->id,
                    'notes' => $request->notes ?? 'Status updated',
                    'status' => $request->status,
                    'actions' => null,
                ]);
            }

            DB::commit();

            return redirect()->route('leads.index')
                ->with('success', 'Lead updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }


    public function destroy($id)
    {
        $this->authorize('user-delete');
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully');
    }
}
