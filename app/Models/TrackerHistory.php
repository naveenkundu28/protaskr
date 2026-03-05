<?php

namespace App\Models;

use App\Traits\TrackerTrait;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackerHistory extends Model
{
    use HasFactory, TrackerTrait;
    protected $guarded = [];

    public function getElapsedTimeFormattedAttribute()
    {
        return $this->_getTotalTime($this->elapsed_time);
    }
}
