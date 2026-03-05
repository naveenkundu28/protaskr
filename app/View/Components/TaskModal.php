<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskModal extends Component
{
    /**
     * Create a new component instance.
     */
    public $projects;
    public $users;
    public function __construct(public $project)
    {
        $this->projects = auth()->user()->hasRole('Admin')
            ? auth()->user()->projects
            : auth()->user()->projectMembers;

        $this->users = collect([]);
        if ($this->project) {
            $this->users = $this->project->sortedUsers();
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.task-modal');
    }
}
