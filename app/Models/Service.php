<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_type',
        'client_id',
        'service_id',
        'month',
        'year',
        'hours',
        'service_status',
        'start_date',
        'end_date',
        'front_resource',
        'working_resource',
        'cancel_reason',
        'replacement_reason',
    ];


    public function frontResource()
    {
        return $this->belongsTo(User::class, 'front_resource')->withTrashed();
    }

    /**
     * Working resources are stored as comma-separated user ids ("3,7,12").
     * Assigning an array (or a single id) stores it in that format.
     */
    protected function workingResource(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => self::normalizeResourceIds($value)
                ? implode(',', self::normalizeResourceIds($value))
                : null,
        );
    }

    /**
     * The working resource user ids as an array of integers.
     */
    public function getWorkingResourceIdsAttribute(): array
    {
        return self::normalizeResourceIds($this->attributes['working_resource'] ?? null);
    }

    /**
     * Users assigned as working resources, in stored order. Call
     * Service::loadWorkingUsers($services) first on lists to avoid a query per row.
     */
    public function getWorkingUsersAttribute()
    {
        if (!$this->relationLoaded('workingUsers')) {
            self::loadWorkingUsers([$this]);
        }

        return $this->getRelation('workingUsers');
    }

    /**
     * Load the working users of many services with a single query.
     */
    public static function loadWorkingUsers($services): void
    {
        $services = collect($services instanceof \Illuminate\Contracts\Pagination\Paginator ? $services->items() : $services);

        $users = User::withTrashed()
            ->whereIn('id', $services->flatMap->working_resource_ids->unique()->values())
            ->get()
            ->keyBy('id');

        foreach ($services as $service) {
            $service->setRelation('workingUsers', collect($service->working_resource_ids)
                ->map(fn ($id) => $users->get($id))
                ->filter()
                ->values());
        }
    }

    public static function normalizeResourceIds($value): array
    {
        $ids = is_array($value) ? $value : explode(',', (string) $value);

        return collect($ids)
            ->map(fn ($id) => (int) trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function scopeWhereHasWorkingResource($query, $userId)
    {
        return $query->whereRaw('FIND_IN_SET(?, working_resource)', [(int) $userId]);
    }

    /**
     * Services that have at least one of the given users as a working resource.
     */
    public function scopeWhereHasAnyWorkingResource($query, array $userIds)
    {
        $userIds = self::normalizeResourceIds($userIds);

        return $query->where(function ($q) use ($userIds) {
            foreach ($userIds as $userId) {
                $q->orWhereRaw('FIND_IN_SET(?, working_resource)', [$userId]);
            }
            if (empty($userIds)) {
                $q->whereRaw('1 = 0');
            }
        });
    }

    /**
     * Services whose working resources match a name / employee id search.
     */
    public function scopeOrWhereWorkingResourceMatches($query, string $search)
    {
        $userIds = User::withTrashed()
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            })
            ->pluck('id')
            ->all();

        return $query->orWhere(fn ($q) => $q->whereHasAnyWorkingResource($userIds));
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
