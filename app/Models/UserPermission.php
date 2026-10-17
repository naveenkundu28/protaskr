<?php

namespace App\Models;

use App\Support\Roles;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $fillable = [
        'user_id',
        'permission',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Give the named permissions to every user whose role has them by default
     * in config/permissions.php. Meant for the migration that ships a new permission.
     */
    public static function grantToDefaultRoles(array $permissions): void
    {
        foreach (User::withTrashed()->with('role')->get() as $user) {
            foreach (array_intersect($permissions, Roles::defaultPermissions($user->role?->name)) as $permission) {
                static::firstOrCreate(['user_id' => $user->id, 'permission' => $permission]);
            }
        }
    }
}
