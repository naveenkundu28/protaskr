<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProjectFolder extends Model { protected $fillable=['project_id','parent_id','name','created_by']; public function project(){return $this->belongsTo(Project::class);} public function parent(){return $this->belongsTo(self::class,'parent_id');} public function creator(){return $this->belongsTo(User::class,'created_by');} }
