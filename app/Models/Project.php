<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Project extends Model {
    use SoftDeletes;
    protected $fillable = ['client_id','name','description','start_date','end_date','project_type','priority','budget_hours','project_manager_id','status','created_by'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','budget_hours'=>'decimal:2'];
    public function client(){ return $this->belongsTo(Client::class); }
    public function manager(){ return $this->belongsTo(User::class, 'project_manager_id'); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
    public function members(){ return $this->belongsToMany(User::class, 'project_members')->withTimestamps(); }
    public function stories(){ return $this->hasMany(Story::class); }
    public function tasks(){ return $this->hasMany(Task::class); }
    public function getProgressAttribute(){ $total=$this->tasks()->count(); return $total ? round($this->tasks()->where('status','completed')->count()*100/$total) : 0; }
}
