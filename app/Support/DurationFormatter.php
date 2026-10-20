<?php

namespace App\Support;

class DurationFormatter
{
    public static function fromHours($hours, string $nullText = '0h 0m'): string
    {
        if ($hours === null || $hours === '') {
            return $nullText;
        }

        $totalMinutes = (int) round((float) $hours * 60);
        $wholeHours = intdiv($totalMinutes, 60);
        $remainingMinutes = $totalMinutes % 60;

        return $wholeHours . 'h ' . $remainingMinutes . 'm';
    }
}
