<?php
namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dates = ['start_time','end_time'];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo(Client::class, 'client_id')->withTrashed();
    }

    public function deliveryProject() { return $this->belongsTo(Project::class, 'project_id'); }
    public function story() { return $this->belongsTo(Story::class); }
    public function dueDateHistories() { return $this->hasMany(TaskDueDateHistory::class)->orderBy('id'); }

    public function getStatusBadgeAttribute()
    {
        $statusBadges = [
            'to_do' => ['label' => 'To Do', 'class' => 'bg-primary'],
            'in_progress' => ['label' => 'In Progress', 'class' => 'bg-danger'],
            'pending' => ['label' => 'Pending', 'class' => 'bg-info'],
            'in_review' => ['label' => 'In Review', 'class' => 'bg-warning'],
            'on_hold' => ['label' => 'On Hold', 'class' => 'bg-danger'],
            'closed' => ['label' => 'Closed', 'class' => 'bg-secondary'],
            'completed' => ['label' => 'Completed', 'class' => 'bg-success'],
        ];

        $status = $this->status;

        if (isset($statusBadges[$status])) {
            return sprintf(
                '<span class="badge %s task-status task-status-%s">%s</span>',
                $statusBadges[$status]['class'],
                $status,
                $statusBadges[$status]['label']
            );
        }

        return '<span class="badge badge-secondary">Unknown</span>';
    }

    public function checklistItems()
    {
        return $this->hasMany(ChecklistItem::class);
    }

    public function getTotalElapsedTime($userId = null)
    {
        $query = $this->trackerHistories();
        $totalSeconds = $query->sum('elapsed_time');

        $interval = CarbonInterval::seconds($totalSeconds)->cascade();
        $hours = $interval->hours;
        $minutes = $interval->minutes;

        return "{$hours} Hrs, {$minutes} Mins";
    }

    public function trackerHistories()
    {
        return $this->hasMany(TrackerHistory::class);
    }
    public function getColorAttribute()
    {
        $duedate = Carbon::parse($this->due_date);

        if ($duedate->isToday()) {
            return '';
        } elseif ($duedate->isPast()) {
            return 'table-danger';
        }
    }
}
