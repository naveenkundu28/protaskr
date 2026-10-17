<?php

namespace App\Support;

/**
 * Single source of truth for role names (stored in the `roles` table).
 * Use these constants instead of hardcoding role strings.
 */
class Roles
{
    public const ADMIN = 'Admin';
    public const RESOURCE = 'Resource';
    public const CLIENT = 'Client';

    public static function all(): array
    {
        return [self::ADMIN, self::RESOURCE, self::CLIENT];
    }

    /**
     * The permissions a role receives by default, as listed in config/permissions.php.
     */
    public static function defaultPermissions(?string $role): array
    {
        return array_keys(array_filter(
            config('permissions', []),
            fn (array $roles) => in_array($role, $roles, true)
        ));
    }
}
