<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyPerformance extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'background_color',
        'font_color',
        'need_a_reason',
        'min',
        'max'
    ];

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }
}
