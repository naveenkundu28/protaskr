<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MigrateOldUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Options:
     *   --update   Also update existing users (matched by email) with data from old DB
     *   --dry-run  Preview what would happen without writing anything
     *
     * @var string
     */
    protected $signature = 'users:migrate-old
                            {--update  : Update existing users instead of skipping them}
                            {--dry-run : Preview changes without writing to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate users from the old DB (time_tracker) into the new DB (task_manager_new). Skips duplicates by email unless --update is passed.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $shouldUpdate = $this->option('update');

        if ($isDryRun) {
            $this->warn('--- DRY RUN: no changes will be saved ---');
        }

        // ----------------------------------------------------------------
        // 1. Fetch all users from the OLD database
        // ----------------------------------------------------------------
        $oldUsers = DB::connection('old_db')
            ->table('users')
            ->get();

        $this->info("Found {$oldUsers->count()} users in old DB.");

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        foreach ($oldUsers as $old) {

            // ----------------------------------------------------------------
            // 2. Map old columns → new columns
            //
            //  OLD DB column         NEW DB column
            //  ------------------    ------------------
            //  name                  name  (first name only in old DB)
            //  last_name             appended to name  → "name lastname"
            //  email                 email
            //  password              password
            //  mobile                phone_no
            //  employee_id           employee_id
            //  joining_date          joining_date
            //  experience            experience
            //  user_type             user_type  (INTERNAL→Internal, EXTERNAL→External)
            //  email_verified_at     email_verified_at
            //  remember_token        remember_token
            //  created_at            created_at
            //  updated_at            updated_at
            //  deleted_at            deleted_at
            // ----------------------------------------------------------------

            $fullName   = trim($old->name . ' ' . ($old->last_name ?? ''));
            $userType   = $this->mapUserType($old->user_type ?? 'INTERNAL');
            $deletedAt  = $this->parseDeletedAt($old->deleted_at);

            $newData = [
                'name'              => $fullName,
                'email'             => $old->email,
                'password'          => $old->password,   // already hashed – copy as-is
                'user_type'         => $userType,
                'employee_id'       => $old->employee_id ?: null,
                'joining_date'      => $this->parseDate($old->joining_date),
                'phone_no'          => $old->mobile ?: null,
                'alt_phone_no'      => null,
                'experience'        => $old->experience ?? null,
                'email_verified_at' => $old->email_verified_at,
                'manual'            => 0,
                'remember_token'    => $old->remember_token,
                'created_at'        => $old->created_at,
                'updated_at'        => $old->updated_at,
                'deleted_at'        => $deletedAt,
                'comment'           => null,
            ];

            // ----------------------------------------------------------------
            // 3. Check if the email already exists in the new DB
            // ----------------------------------------------------------------
            $existing = DB::connection('mysql')
                ->table('users')
                ->where('email', $old->email)
                ->first();

            if ($existing) {
                if ($shouldUpdate) {
                    // Remove created_at from the update payload
                    $updateData = array_diff_key($newData, array_flip(['created_at']));

                    $this->line("[UPDATE]  {$old->email}");

                    if (! $isDryRun) {
                        DB::connection('mysql')
                            ->table('users')
                            ->where('email', $old->email)
                            ->update($updateData);
                    }

                    $updated++;
                } else {
                    $this->line("[SKIP]    {$old->email}  (already exists)");
                    $skipped++;
                }
            } else {
                $this->line("[INSERT]  {$old->email}");

                if (! $isDryRun) {
                    DB::connection('mysql')
                        ->table('users')
                        ->insert($newData);
                }

                $inserted++;
            }
        }

        // ----------------------------------------------------------------
        // 4. Summary
        // ----------------------------------------------------------------
        $this->newLine();
        $this->info('Migration complete' . ($isDryRun ? ' (dry run)' : '') . ':');
        $this->table(
            ['Action', 'Count'],
            [
                ['Inserted', $inserted],
                ['Updated',  $updated],
                ['Skipped',  $skipped],
            ]
        );

        return self::SUCCESS;
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Convert old DB user_type (INTERNAL / EXTERNAL / null)
     * to new DB convention (Internal / External / null).
     */
    private function mapUserType(?string $type): ?string
    {
        return match (strtoupper((string) $type)) {
            'INTERNAL' => 'Internal',
            'EXTERNAL' => 'External',
            default    => null,
        };
    }

    /**
     * The old DB stores joining_date as varchar with mixed formats
     * (e.g. "2023-04-06" or "04-Jun-1990"). Try to normalise to Y-m-d.
     */
    private function parseDate(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * The old DB deleted_at is a date column (not timestamp).
     * Cast to a full timestamp string so it fits the new DB timestamp column.
     */
    private function parseDeletedAt(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}
