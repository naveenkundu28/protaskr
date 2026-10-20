<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Roles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'employee_id',
        'joining_date',
        'phone_no',
        'alt_phone_no',
        'experience',
        'shift_start_time',
        'shift_end_time',
        'is_show_report',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var array
     */
    protected ?array $permissionNames = null;

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Names of the permissions this user holds, loaded once per request.
     */
    public function permissionNames(): array
    {
        return $this->permissionNames ??= $this->permissions()->pluck('permission')->all();
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissionNames(), true);
    }

    /**
     * Replace this user's permissions with the given names. Names that are
     * not in config/permissions.php are ignored.
     */
    public function syncPermissions(array $permissions): void
    {
        $permissions = array_values(array_unique(array_intersect($permissions, array_keys(config('permissions', [])))));

        $this->permissions()->whereNotIn('permission', $permissions)->delete();

        foreach (array_diff($permissions, $this->permissions()->pluck('permission')->all()) as $permission) {
            $this->permissions()->create(['permission' => $permission]);
        }

        $this->permissionNames = null;
    }

    /**
     * Give this user exactly the default permissions of their role.
     */
    public function assignDefaultPermissions(): void
    {
        $this->syncPermissions(Roles::defaultPermissions($this->role?->name));
    }

    public function hasRole(string $name): bool
    {
        return $this->role?->name === $name;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Roles::ADMIN);
    }

    public function scopeOfRole($query, string $name)
    {
        return $query->whereHas('role', fn($role) => $role->where('name', $name));
    }

    /**
     * Get the projects for the user.
     */
    public function projects()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * The client this login belongs to, for users with the Client role.
     */
    public function client()
    {
        return $this->hasOne(Client::class, 'profile_user_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function projectMembers()
    {
        return $this->belongsToMany(Client::class, 'client_teams', 'user_id', 'client_id');
    }

    /**
     * Get all of the trackerHistories for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trackerHistories(): HasMany
    {
        return $this->hasMany(TrackerHistory::class);
    }

    public function primarySkills()
    {
        return $this->belongsToMany(Skill::class, 'user_primary_skills')->withTimestamps();
    }

    public function secondarySkills()
    {
        return $this->belongsToMany(Skill::class, 'user_secondary_skills')->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'user_tag')->withTimestamps();
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }
}
