<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadStatus;
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
        $dateFilter = $request->input('date_filter', 'today');

        $leads = Lead::with(['requirement', 'tlUser', 'teamUser', 'latestNotesHistory.leadStatus', 'skills'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('lead', 'like', "%{$search}%");
            })
            ->when($dateFilter, function ($query) use ($dateFilter) {
                $now = now();
                switch ($dateFilter) {
                    case 'all':
                        // No filter
                        break;
                    case 'today':
                        $query->where(function ($query) use ($now) {
                            $query->whereDate('leads.updated_at', $now->toDateString())
                                ->orWhereHas('notesHistories', function ($query) use ($now) {
                                    $query->whereDate('updated_at', $now->toDateString());
                                });
                        });
                        break;
                    case 'yesterday':
                        $date = $now->copy()->subDay()->toDateString();
                        $query->where(function ($query) use ($date) {
                            $query->whereDate('leads.updated_at', $date)
                                ->orWhereHas('notesHistories', function ($query) use ($date) {
                                    $query->whereDate('updated_at', $date);
                                });
                        });
                        break;
                    case 'current_week':
                        $start = $now->copy()->startOfWeek();
                        $end = $now->copy()->endOfWeek();
                        $query->where(function ($query) use ($start, $end) {
                            $query->whereBetween('leads.updated_at', [$start, $end])
                                ->orWhereHas('notesHistories', function ($query) use ($start, $end) {
                                    $query->whereBetween('updated_at', [$start, $end]);
                                });
                        });
                        break;
                    case 'last_week':
                        $start = $now->copy()->subWeek()->startOfWeek();
                        $end = $now->copy()->subWeek()->endOfWeek();
                        $query->where(function ($query) use ($start, $end) {
                            $query->whereBetween('leads.updated_at', [$start, $end])
                                ->orWhereHas('notesHistories', function ($query) use ($start, $end) {
                                    $query->whereBetween('updated_at', [$start, $end]);
                                });
                        });
                        break;
                    case 'last_15_days':
                        $date = $now->copy()->subDays(15)->toDateString();
                        $query->where(function ($query) use ($date) {
                            $query->where('leads.updated_at', '>=', $date)
                                ->orWhereHas('notesHistories', function ($query) use ($date) {
                                    $query->where('updated_at', '>=', $date);
                                });
                        });
                        break;
                    case 'last_month':
                        $date = $now->copy()->subDays(30);
                        $query->where(function ($query) use ($date) {
                            $query->where('leads.updated_at', '>=', $date)
                                ->orWhereHas('notesHistories', function ($query) use ($date) {
                                    $query->where('updated_at', '>=', $date);
                                });
                        });
                        break;
                        // default: no filter
                }
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('leads.index', compact('leads'))
            ->with('i', ($request->input('page', 1) - 1) * 50)
            ->with('current_filter', $dateFilter);
    }
    public function create()
    {
        $this->authorize('user-create');
        $requirements = Requirement::all();
        $skills = Skill::all();
        $leadStatuses = LeadStatus::all();

        // Only users who can be TL (example filter)
        $users = User::where('show_leads', true)->get();

        return view('leads.create', compact('requirements', 'users', 'skills', 'leadStatuses'));
    }

    public function store(Request $request)
    {
        $this->authorize('user-create');
        $request->validate([
            'lead'      => 'required|string|max:255',
            'lead_status_id' => 'required|exists:lead_statuses,id',
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
            if ($request->notes || $request->lead_status_id) {
                NotesHistory::create([
                    'lead_id' => $lead->id,
                    'notes'   => $request->notes ?? 'Status updated',
                    'lead_status_id'  => $request->lead_status_id,
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
        $leadStatuses = LeadStatus::all();

        // Latest status from notes_history
        $latestStatusId = optional(
            $lead->notesHistories()->latest()->first()
        )->lead_status_id;

        $skills = Skill::all();

        return view('leads.edit', compact(
            'lead',
            'users',
            'requirements',
            'latestStatusId',
            'latestNote',
            'skills',
            'leadStatuses'
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('user-edit');
        $lead = Lead::findOrFail($id);

        $request->validate([
            'lead'      => 'required|string|max:255',
            'lead_status_id' => 'required|exists:lead_statuses,id',
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
            if ($request->filled('notes') || $request->filled('lead_status_id')) {
                NotesHistory::create([
                    'lead_id' => $lead->id,
                    'notes' => $request->notes ?? 'Status updated',
                    'lead_status_id' => $request->lead_status_id,
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
