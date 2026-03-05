<?php

namespace App\Traits;

use App\Models\TrackerHistory;
use Carbon\CarbonInterval;

trait TrackerTrait
{
    public function _getTotalTrackerTime($user_id, $task_id)
    {
        $totalTime = TrackerHistory::query()
            ->where('user_id', $user_id)
            ->where('task_id', $task_id)
            ->sum('elapsed_time');

            return $this->_getTotalTime($totalTime);
    }

    public function _getTotalTime($seconds): String
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
    
        return str(str($hours)->padLeft(2, 0))
            ->append(':')
            ->append(str($minutes)->padLeft(2, 0))
            ->append(':')
            ->append(str($seconds)->padLeft(2, 0))
            ->__toString();
    }
}
