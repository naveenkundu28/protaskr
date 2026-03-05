<?php

namespace App\Http\Controllers\Api;

use App\Traits\TrackerTrait;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TrackerController extends Controller
{
    use TrackerTrait;

    public function report(Request $request)
    {
        return User::query()
            ->withSum(['trackerHistories as tracker_time' => function ($query) {
                $query->when(request('date'), function ($query) {
                    $query->whereDate('created_at', Carbon::parse(request('date')));
                });
                $query->when(!request('date'), function ($query) {
                    $query->whereDate('created_at', now());
                });
            }], 'elapsed_time')
            ->when($request->get('users'), function ($query) {
                $query->whereIn('email', request('users'));
            })
            ->get()
            ->map(function($item){
                $item->tracker_time = $this->_getTotalTime($item->tracker_time);
                return $item;
            });
    }
}
