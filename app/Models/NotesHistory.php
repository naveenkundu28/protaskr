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
        'status',
        'actions',
    ];
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
