<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Give every user exactly the default permissions of their role, as listed
     * in config/permissions.php: missing ones are granted, extra ones withdrawn.
     * The same reset can be run at any time with "php artisan permissions:reset".
     */
    public function up(): void
    {
        Artisan::call('permissions:reset');
    }

    public function down(): void
    {
        // the previous per-user permissions are not kept, so there is nothing to restore
    }
};
