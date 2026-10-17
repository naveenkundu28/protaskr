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
    public function getProgressColorClassAttribute(): string { return static::progressColorClass($this->progress); }
    public function getProgressColorHexAttribute(): string { return static::progressColorHex($this->progress); }
    public static function progressColorClass($progress): string {
        $p = (float) $progress;
        if ($p <= 20) return 'progress-danger';
        if ($p <= 40) return 'progress-purple';
        if ($p <= 60) return 'progress-success';
        return 'progress-primary';
    }
    public static function progressColorHex($progress): string {
        $p = (float) $progress;
        if ($p <= 20) return '#dc2626';
        if ($p <= 40) return '#9333ea';
        if ($p <= 60) return '#16a34a';
        return '#1469e8';
    }
}
