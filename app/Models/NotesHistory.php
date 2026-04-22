<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotesHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'notes',
        'lead_status_id',
        'actions',
    ];
    
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function leadStatus()
    {
        return $this->belongsTo(LeadStatus::class);
    }
}
