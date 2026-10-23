<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskMentionNotification extends Model
{
    protected $fillable = ['user_id', 'task_id', 'chat_id', 'mentioned_by', 'message_excerpt', 'read_at'];
    protected $casts = ['read_at' => 'datetime'];
    public function task() { return $this->belongsTo(Task::class); }
    public function sender() { return $this->belongsTo(User::class, 'mentioned_by')->withTrashed(); }
}
