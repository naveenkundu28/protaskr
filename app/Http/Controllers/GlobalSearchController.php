<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Story;
use App\Models\Task;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $term = mb_substr($term, 0, 100);
        $user = $request->user();
        $isAdmin = $user->hasRole(Roles::ADMIN);
        $results = collect();

        if ($isAdmin) {
            $results = $results->concat(Client::query()
                ->where(fn ($query) => $query->where('name', 'like', "%{$term}%")
                    ->orWhere('company_name', 'like', "%{$term}%")
                    ->orWhere('client_email', 'like', "%{$term}%"))
                ->orderBy('name')->limit(5)->get(['id', 'name', 'company_name'])
                ->map(fn (Client $client) => $this->result('Client', $client->name, $client->company_name, route('clients.show', $client))));

            $results = $results->concat(User::query()
                ->where(fn ($query) => $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('employee_id', 'like', "%{$term}%"))
                ->orderBy('name')->limit(5)->get(['id', 'name', 'email', 'employee_id'])
                ->map(fn (User $match) => $this->result('User', $match->name, $match->employee_id ?: $match->email, route('users.show', $match))));

            $results = $results->concat(Lead::query()
                ->where('lead', 'like', "%{$term}%")
                ->latest()->limit(5)->get(['id', 'lead'])
                ->map(fn (Lead $lead) => $this->result('Lead', $lead->lead, 'Lead record', route('leads.edit', $lead))));
        }

        $visibleProjects = Project::query();
        if (!$isAdmin) {
            if ($user->hasRole(Roles::RESOURCE)) {
                $visibleProjects->whereHas('members', fn ($query) => $query->where('users.id', $user->id));
            } elseif ($user->hasRole(Roles::CLIENT)) {
                $visibleProjects->whereHas('client', fn ($query) => $query->where('profile_user_id', $user->id));
            } else {
                return response()->json(['results' => []]);
            }
        }

        $visibleProjectIds = (clone $visibleProjects)->select('projects.id');

        $projects = (clone $visibleProjects)
            ->where(fn ($query) => $query->where('name', 'like', "%{$term}%")
                ->orWhereHas('client', fn ($clientQuery) => $clientQuery->where('name', 'like', "%{$term}%")))
            ->with('client:id,name')
            ->orderBy('name')->limit(6)->get(['id', 'name', 'client_id']);
        $results = $results->concat($projects->map(fn (Project $project) => $this->result(
            'Project', $project->name, $project->client?->name ?: 'Internal project', route('projects.show', $project)
        )));

        $stories = Story::query()
            ->whereIn('project_id', $visibleProjectIds)
            ->where(fn ($query) => $query->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%"))
            ->with('project:id,name')
            ->orderBy('title')->limit(6)->get(['id', 'title', 'project_id']);
        $results = $results->concat($stories->map(fn (Story $story) => $this->result(
            'Story', $story->title, $story->project?->name ?: 'Project', route('stories.show', $story)
        )));

        $tasks = Task::query()
            ->whereIn('project_id', $visibleProjectIds)
            ->where(fn ($query) => $query->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%"))
            ->with(['deliveryProject:id,name', 'story:id,title'])
            ->orderBy('title')->limit(6)->get(['id', 'title', 'project_id', 'story_id']);
        $results = $results->concat($tasks->map(fn (Task $task) => $this->result(
            'Task', $task->title, ($task->deliveryProject?->name ?: 'Project') . ($task->story ? ' · ' . $task->story->title : ''), route('tasks.show', $task)
        )));

        return response()->json(['results' => $results->take(18)->values()]);
    }

    private function result(string $type, string $title, ?string $detail, string $url): array
    {
        return [
            'type' => $type,
            'title' => $title,
            'detail' => $detail,
            'url' => $url,
        ];
    }
}
