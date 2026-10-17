<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProjectFile extends Model { protected $fillable=['project_id','folder_id','name','path','mime_type','size','uploaded_by']; public function project(){return $this->belongsTo(Project::class);} public function folder(){return $this->belongsTo(ProjectFolder::class);} public function uploader(){return $this->belongsTo(User::class,'uploaded_by');} }
