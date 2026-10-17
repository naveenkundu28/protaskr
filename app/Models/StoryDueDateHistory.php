<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoryDueDateHistory extends Model
{
    protected $fillable = ['old_due_date', 'new_due_date', 'changed_by'];

    protected $casts = [
        'old_due_date' => 'date',
        'new_due_date' => 'date',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
