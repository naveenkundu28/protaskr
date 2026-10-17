<?php

namespace App\Exports;

use App\Models\Task;
use App\Traits\TrackerTrait;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;
class TaskExport implements FromCollection, WithHeadings
{
    use TrackerTrait;
    protected $projectid;
    protected $fromDate;
    protected $toDate;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct($fromDate, $toDate, $projectid)
    {
        $this->projectid = $projectid;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }
    public function collection()
    {
        $user = Auth::user();
        return Task::query()
            ->with('project', 'user')
            ->whereHas('project', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when($this->projectid != null, function ($query) {
                $query->where('client_id', $this->projectid);
            })
            ->whereBetween('created_at', [
                $this->fromDate, 
                Carbon::parse($this->toDate)->endOfDay()
            ])
            ->get()->map(function ($task) {
                return [
                    'Title' => $task->title,
                    'Project' => optional($task->project)->name,
                    'Due Date' => $task->due_date ? $task->due_date : null,
                    'Assigned To' => optional($task->user)->name,
                    'Time' => $this->_getTotalTrackerTime($task->user_id, $task->id),
                    'Status' => $task->status,
                ];
            });
    }
    public function headings(): array
    {

        return ["Title", "Project", "Due Date", "Assigned To", "Time", "Status"];

    }
}
