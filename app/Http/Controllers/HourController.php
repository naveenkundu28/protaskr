<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hour;

class HourController extends Controller
{
    public function create()
    {
        $this->authorize('user-create');
        $currentYear = date('Y');
        $hours = Hour::where('year', $currentYear)->get()->keyBy('month');
        $years = range($currentYear - 5, $currentYear + 5);

        $months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        return view('hours.create', compact('years', 'months', 'currentYear', 'hours'));
    }

    public function store(Request $request)
    {
        $this->authorize('user-create');
        $months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        $rules = [
            'year' => 'required|digits:4',
        ];

        foreach ($months as $month) {
            $rules["months.$month"] = 'nullable|integer|min:0|max:744';
        }

        $messages = [
            // 'months.*.required' => 'Please enter hours for :attribute',
            'months.*.integer' => 'Hours must be a number.',
            'months.*.min' => 'Hours cannot be negative.',
            'months.*.max' => 'Hours cannot exceed 744 in a month.',
        ];

        $attributes = [];

        foreach ($months as $month) {
            $attributes["months.$month"] = $month;
        }

        $validated = $request->validate($rules, $messages, $attributes);


        foreach ($request->months as $month => $hours) {

            if ($hours !== '' && $hours !== null) {

                Hour::updateOrCreate(
                    [
                        'year' => $request->year,
                        'month' => $month
                    ],
                    [
                        'hours' => $hours
                    ]
                );
            } else {

                Hour::where('year', $request->year)
                    ->where('month', $month)
                    ->delete();
            }
        }

        return redirect()->route('master.hours.create')->with('success', 'Master hours saved successfully');
    }

    public function getHoursByYear($year)
    {
        $hours = Hour::where('year', $year)->get()->keyBy('month');

        return response()->json($hours);
    }
}
