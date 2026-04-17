<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    // app/Models/Lead.php

    protected $fillable = [
        'lead',
        'tl',
        'team',
        'requirement_id',
        'claimed_group',
        'call_done',
        'cam_member',
        'date',
        'jd',
    ];

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function tlUser()
    {
        return $this->belongsTo(User::class, 'tl');
    }

    public function teamUser()
    {
        return $this->belongsTo(User::class, 'team');
    }

    public function notesHistories()
    {
        return $this->hasMany(NotesHistory::class);
    }
}
