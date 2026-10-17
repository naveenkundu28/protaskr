<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'profile_user_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'budget',
        'deleted_at',
        'client_email',
        'client_code',
        'company_name',
        'email_updated_by',
        'email_updated_at',
        'type'
    ];

    protected $dates = [
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->belongsTo(User::class, 'profile_user_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function emailUpdatedBy()
    {
        return $this->belongsTo(User::class, 'email_updated_by');
    }

    // public function getStatusAttribute()
    // {
    //     $today = Carbon::now();

    //     if ($this->start_date && $today->lt($this->start_date)) {
    //         return 'pending';
    //     }

    //     if ($this->end_date && $this->end_date->lt($today)) {
    //         $unfinishedTasks = $this->tasks()->where('status', '!=', 'completed')->count();
    //         return $unfinishedTasks > 0 ? 'unfinished' : 'finished';
    //     }

    //     return 'on_going';
    // }

    public function teamProjects()
    {
        // return $this->belongsToMany(ClientTeam::class, 'client_teams', 'client_id', 'user_id');
        return $this->belongsToMany(User::class, 'client_teams', 'client_id', 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'client_teams', 'client_id', 'user_id');
    }
    public function allUsers()
    {
        return $this->belongsToMany(User::class, 'client_teams', 'client_id', 'user_id')->withTrashed();
    }

    public function sortedUsers()
    {
        return $this->users
            ->map(function ($user) {
                if ($user->id == auth()->id()) {
                    $user->name = 'Self';
                }
                return $user;
            })
            ->sortByDesc(function ($user) {
                return $user->id == auth()->id();
            })
            ->values();
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
