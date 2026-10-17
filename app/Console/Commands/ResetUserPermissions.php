<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetUserPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:reset {--user= : Only reset the user with this ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset users to the default permissions of their role (config/permissions.php)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = User::withTrashed()->with('role');

        if ($this->option('user') !== null) {
            $query->whereKey($this->option('user'));
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->error($this->option('user') !== null
                ? 'No user found with ID ' . $this->option('user') . '.'
                : 'No users found.');

            return self::FAILURE;
        }

        // missing defaults are granted, anything beyond the role's defaults is withdrawn
        foreach ($users as $user) {
            $user->assignDefaultPermissions();
        }

        $this->info('Default permissions applied to ' . $users->count() . ' user(s).');

        return self::SUCCESS;
    }
}
