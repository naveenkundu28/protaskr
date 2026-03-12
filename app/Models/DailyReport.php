<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'date',
        'time',
        'daily_performance_id',
        'reason',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function performance()
    {
        return $this->belongsTo(DailyPerformance::class,'daily_performance_id');
    }
}
