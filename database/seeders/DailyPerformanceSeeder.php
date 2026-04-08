<?php

namespace Database\Seeders;

use App\Models\DailyPerformance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DailyPerformanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [

            [
                'title' => 'Excellent',
                'background_color' => '#145a32',
                'font_color' => '#ffffff',
                'need_a_reason' => 0,
                'min' => 9,
                'max' => 13
            ],

            [
                'title' => 'Good',
                'background_color' => '#52be80',
                'font_color' => '#ffffff',
                'need_a_reason' => 0,
                'min' => 8,
                'max' => 9
            ],

            [
                'title' => 'Average',
                'background_color' => '#f9e79f',
                'font_color' => '#000000',
                'need_a_reason' => 0,
                'min' => 7,
                'max' => 8
            ],

            [
                'title' => 'Below Average',
                'background_color' => '#fef9e7',
                'font_color' => '#000000',
                'need_a_reason' => 1,
                'min' => 5,
                'max' => 7
            ],

            [
                'title' => 'Poor',
                'background_color' => '#d98880',
                'font_color' => '#ffffff',
                'need_a_reason' => 1,
                'min' => 0,
                'max' => 5
            ],
            [
                'title' => 'Absent',
                'background_color' => '#641e16',
                'font_color' => '#ffffff',
                'need_a_reason' => 0,
                'min' => 0,
                'max' => 5
            ],
            [
                'title' => "Didn't Track",
                'background_color' => '#566573',
                'font_color' => '#ffffff',
                'need_a_reason' => 0,
                'min' => 0,
                'max' => 0
            ],
            [
                'title' => "Wrong Entry",
                'background_color' => '#002589',
                'font_color' => '#F5F5F5',
                'need_a_reason' => 0,
                'min' => 13,
                'max' => 1000
            ],
        ];

        foreach ($data as $item) {

            DailyPerformance::updateOrCreate(
                ['title' => $item['title']],
                $item
            );

        }
    }
}
