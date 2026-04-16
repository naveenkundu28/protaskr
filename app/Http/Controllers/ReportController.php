<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Hour;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('user-show');

        // $fromDate = $request->filled('from')
        //     ? Carbon::parse($request->input('from'))->startOfDay()
        //     : Carbon::now()->startOfMonth()->startOfDay();

        // $toDate = $request->filled('to')
        //     ? Carbon::parse($request->input('to'))->endOfDay()
        //     : Carbon::now()->endOfDay();

        $date = Carbon::now();

        $fromDate = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : $date->copy()->startOfQuarter()->startOfDay();

        $toDate = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : $date->copy()->endOfQuarter()->endOfDay();

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        $newClients = Client::query()
            ->with(['users' => function ($query) {
                $query->withTrashed();
            }])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderByDesc('created_at')
            ->get();

        $currentClients = Client::query()
            ->with(['users' => function ($query) {
                $query->withTrashed();
            }])
            ->whereNull('deleted_at')
            ->where('status', '!=', 'completed')
            ->whereBetween('start_date', [$fromDate->toDateString(), $toDate->toDateString()])
            
            // ->where(function ($query) use ($fromDate, $toDate) {
            //     $query->whereNull('start_date')
            //         ->orWhereDate('start_date', '<=', $toDate->toDateString());
            // })
            // ->where(function ($query) use ($fromDate) {
            //     $query->whereNull('end_date')
            //         ->orWhereDate('end_date', '>=', $fromDate->toDateString());
            // })
            ->orderBy('name')
            ->get();

        $newServices = Service::with(['client', 'frontResource', 'workingResource'])
            ->where('service_status', 'new')
            ->where('service_type', 'direct')
            // ->whereBetween('created_at', [$fromDate, $toDate])
            ->whereBetween('start_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->orderByDesc('start_date')
            ->get();

        $completedServices = Service::with(['client', 'frontResource', 'workingResource'])
            ->where('service_status', 'completed')
            ->where('service_type', 'direct')
            ->whereBetween('end_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->orderByDesc('end_date')
            ->get();
            

        $cancelledServices = Service::with(['client', 'frontResource', 'workingResource'])
            ->where(function ($query) {
                $query->where('service_status', 'canceled')
                    ->orWhere('service_status', 'replacement');
            })
            ->where('service_type', 'direct')
            ->whereBetween('end_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->orderByDesc('end_date')
            ->get();

        $newJoiningsCount = User::query()
            ->whereDate('joining_date', '>=', $fromDate->toDateString())
            ->whereDate('joining_date', '<=', $toDate->toDateString())
            ->count();

        $resignationCount = User::onlyTrashed()
            ->whereDate('deleted_at', '>=', $fromDate->toDateString())
            ->whereDate('deleted_at', '<=', $toDate->toDateString())
            ->count();

        // dd($newServices);

        $newServices = $this->appendServiceWorkType($newServices, 'created_at');
        $completedServices = $this->appendServiceWorkType($completedServices, 'end_date');
        $cancelledServices = $this->appendServiceWorkType($cancelledServices, 'end_date');

        return view('reports.index', [
            'fromDate' => $fromDate->toDateString(),
            'toDate' => $toDate->toDateString(),
            'newJoiningsCount' => $newJoiningsCount,
            'resignationCount' => $resignationCount,
            'newClients' => $newClients,
            'currentClients' => $currentClients,
            'newServices' => $newServices,
            'completedServices' => $completedServices,
            'cancelledServices' => $cancelledServices,
        ]);
    }


    private function appendServiceWorkType($services, string $dateField)
    {
        $groupedTotals = [];

        foreach ($services as $service) {
            $referenceDate = $this->resolveServiceReferenceDate($service, $dateField);

            if (!$referenceDate) {
                continue;
            }

            // $groupKey = $service->service_id.'|'.$referenceDate->format('Y-m');
            // $groupedTotals[$groupKey] = ($groupedTotals[$groupKey] ?? 0) + (float) $service->hours;

        }

        return $services->map(function ($service) use ($groupedTotals, $dateField) {
            $resources = Service::with(['workingResource'])
                ->where('service_id', $service->service_id)
                ->where('service_type', '!=', 'direct')
                ->get()
                ->groupBy('working_resource')
                ->map(function ($items) {
                    $resource = $items->first()->workingResource;

                    return [
                        'resource_name' => $resource ? $resource->name : null,
                        'total_hours'   => $items->sum('hours'),
                    ];
                })
                ->values()
                ->toArray();

            $referenceDate = $this->resolveServiceReferenceDate($service, $dateField);
            $service->service_total_hours = (float) $service->hours;
            $service->service_work_type = 'ADHOC';
            $service->service_other_resources = $resources;


            if (!$referenceDate) {
                return $service;
            }

            // $groupKey = $service->service_id.'|'.$referenceDate->format('Y-m');
            // $totalHours = (float) ($groupedTotals[$groupKey] ?? 0);
            $masterHours = Hour::where('year', $referenceDate->year)
                ->where('month', $referenceDate->format('F'))
                ->value('hours');

            // $service->service_total_hours = $totalHours;
            $service->service_work_type = $this->resolveWorkType($service->service_total_hours, $masterHours);
            return $service;
        });
    }

    private function resolveServiceReferenceDate(Service $service, string $dateField): ?Carbon
    {
        $value = $service->{$dateField} ?: $service->created_at;

        return $value ? Carbon::parse($value) : null;
    }

    private function resolveWorkType(float $totalHours, $masterHours): string
    {
        if (!$masterHours || $totalHours <= 0) {
            return 'ADHOC';
        }

        $halfHours = $masterHours / 2;

        if ($totalHours > $masterHours) {
            return 'FULL +';
        }

        if ($totalHours == $masterHours) {
            return 'FULL';
        }

        if ($totalHours == $halfHours) {
            return 'HALF';
        }

        if ($totalHours > $halfHours) {
            return 'HALF +';
        }

        return 'ADHOC';
    }
}
