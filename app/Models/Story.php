<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Story extends Model {
    use SoftDeletes;
    protected $fillable=['project_id','title','description','acceptance_criteria','story_points','priority','status','due_date','created_by'];
    protected $casts=['due_date'=>'date'];
    public function project(){ return $this->belongsTo(Project::class); }
    public function tasks(){ return $this->hasMany(Task::class); }
    public function creator(){ return $this->belongsTo(User::class,'created_by'); }
    public function dueDateHistories(){ return $this->hasMany(StoryDueDateHistory::class)->orderBy('id'); }
}
