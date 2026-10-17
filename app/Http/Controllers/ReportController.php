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
        $this->authorize('reports-access');

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
            ->whereBetween('start_date', [$fromDate, $toDate])
            ->orderByDesc('created_at')
            ->get();

        $currentClients = Client::query()
            ->with(['users' => function ($query) {
                $query->withTrashed();
            }])
            ->whereNull('deleted_at')
            ->where('status', '!=', 'completed')
            ->where('type', 'client')
            // ->whereBetween('start_date', [$fromDate->toDateString(), $toDate->toDateString()])
            
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

        $newServices = Service::with(['client', 'frontResource'])
            ->where('service_status', 'new')
            ->where('service_type', 'direct')
            ->whereNotNull('month')
            ->whereNotNull('year')
            // Filter by service month/year by converting them into a comparable date.
            ->whereRaw(
                "COALESCE(
                    STR_TO_DATE(CONCAT('1 ', month, ' ', year), '%e %M %Y'),
                    STR_TO_DATE(CONCAT('1 ', month, ' ', year), '%e %b %Y'),
                    STR_TO_DATE(CONCAT(year, '-', month, '-01'), '%Y-%c-%d')
                ) BETWEEN ? AND ?",
                [$fromDate->toDateString(), $toDate->toDateString()]
            )
            ->orderByRaw("COALESCE(
                STR_TO_DATE(CONCAT('1 ', month, ' ', year), '%e %M %Y'),
                STR_TO_DATE(CONCAT('1 ', month, ' ', year), '%e %b %Y'),
                STR_TO_DATE(CONCAT(year, '-', month, '-01'), '%Y-%c-%d')
            ) DESC")
            ->get();

        $completedServices = Service::with(['client', 'frontResource'])
            ->where('service_status', 'completed')
            ->where('service_type', 'direct')
            ->whereBetween('end_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->orderByDesc('end_date')
            ->get();
            

        $cancelledServices = Service::with(['client', 'frontResource'])
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

        $newServices = $this->appendServiceWorkType($newServices, 'service_month_year');
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
        Service::loadWorkingUsers($services);

        return $services->map(function ($service) use ($groupedTotals, $dateField) {
            $referenceDate = $this->resolveServiceReferenceDate($service, $dateField);
            $service->service_total_hours = (float) $service->hours;
            $service->service_work_type = 'ADHOC';
            $service->service_other_resources = [];


            if (!$referenceDate) {
                return $service;
            }

            $groupKey = $service->service_id.'|'.$referenceDate->format('Y-m');
            if (!array_key_exists($groupKey, $groupedTotals)) {
                $groupedTotals[$groupKey] = (float) Service::query()
                    ->where('service_id', $service->service_id)
                    ->where(function ($query) use ($referenceDate) {
                        $query->where(function ($q) use ($referenceDate) {
                            $q->where('month', $referenceDate->format('n'))
                                ->where('year', $referenceDate->year);
                        })->orWhere(function ($q) use ($referenceDate) {
                            $q->where('month', $referenceDate->format('m'))
                                ->where('year', $referenceDate->year);
                        })->orWhere(function ($q) use ($referenceDate) {
                            $q->whereRaw('LOWER(month) = ?', [strtolower($referenceDate->format('F'))])
                                ->where('year', $referenceDate->year);
                        })->orWhere(function ($q) use ($referenceDate) {
                            $q->whereRaw('LOWER(month) = ?', [strtolower($referenceDate->format('M'))])
                                ->where('year', $referenceDate->year);
                        });
                    })
                    ->sum('hours');
            }

            $resources = Service::query()
                ->where('service_id', $service->service_id)
                ->where('service_type', '!=', 'direct')
                ->where(function ($query) use ($referenceDate) {
                    $query->where(function ($q) use ($referenceDate) {
                        $q->where('month', $referenceDate->format('n'))
                            ->where('year', $referenceDate->year);
                    })->orWhere(function ($q) use ($referenceDate) {
                        $q->where('month', $referenceDate->format('m'))
                            ->where('year', $referenceDate->year);
                    })->orWhere(function ($q) use ($referenceDate) {
                        $q->whereRaw('LOWER(month) = ?', [strtolower($referenceDate->format('F'))])
                            ->where('year', $referenceDate->year);
                    })->orWhere(function ($q) use ($referenceDate) {
                        $q->whereRaw('LOWER(month) = ?', [strtolower($referenceDate->format('M'))])
                            ->where('year', $referenceDate->year);
                    });
                })
                ->get()
                ->tap(fn ($items) => Service::loadWorkingUsers($items))
                ->flatMap(function ($item) {
                    // One entry per assigned resource; a service without resources keeps a single blank entry.
                    $users = $item->working_users;

                    return ($users->isEmpty() ? collect([null]) : $users)->map(fn ($user) => [
                        'key' => $user?->id,
                        'resource_name' => $user?->name,
                        'hours' => (float) $item->hours,
                    ]);
                })
                ->groupBy('key')
                ->map(fn ($entries) => [
                    'resource_name' => $entries->first()['resource_name'],
                    'total_hours'   => (float) $entries->sum('hours'),
                ])
                ->values()
                ->toArray();
            $service->service_other_resources = $resources;

            $totalHours = (float) ($groupedTotals[$groupKey] ?? 0);
            $masterHours = Hour::where('year', $referenceDate->year)
                ->where('month', $referenceDate->format('F'))
                ->value('hours');

            $service->service_total_hours = $totalHours;
            $service->service_work_type = $this->resolveWorkType($service->service_total_hours, $masterHours);
            return $service;
        });
    }

    private function resolveServiceReferenceDate(Service $service, string $dateField): ?Carbon
    {
        if ($dateField === 'service_month_year') {
            if (!empty($service->month) && !empty($service->year)) {
                $month = trim((string) $service->month);
                $year = trim((string) $service->year);

                $monthValue = ctype_digit($month) ? (int) $month : Carbon::parse("1 {$month}")->month;
                return Carbon::createFromDate((int) $year, $monthValue, 1)->startOfDay();
            }
        }

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
