<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Migrates clients and their resource assignments from the old DB (time_tracker)
 * to the new DB (task_manager_new).
 *
 * Tables migrated:
 *   OLD DB                     NEW DB
 *   ─────────────────────────  ───────────────────────────────────────────────
 *   clients                →   clients
 *   client_resources       →   services
 *
 * ── clients column mapping ──────────────────────────────────────────────────
 *   OLD                         NEW
 *   client_name             →   name
 *   client_email            →   client_email
 *   client_code             →   client_code
 *   starting_date           →   start_date
 *   end_date                →   end_date
 *   client_type_id          →   status   (1=in_progress, 2=not_started, 3=completed)
 *   is_external             →   client_type  (1=external, 0=internal)
 *   created_at / updated_at →   created_at / updated_at
 *   user_id                 →   always 1 (Admin in new DB)
 *
 * ── client_resources → services column mapping ───────────────────────────────
 *   OLD                         NEW
 *   client_id               →   client_id          (remapped via clientIdMap)
 *   working_user_id         →   working_resource   (remapped via userIdMap)
 *   hire_user_id            →   front_resource     (remapped via userIdMap)
 *   service_id              →   service_id
 *   hours                   →   hours
 *   month (name)            →   month              (converted to month number)
 *   year                    →   year
 *   start_date ("YY-MM-DD") →   start_date         (normalised to YYYY-MM-DD)
 *   end_date   ("YY-MM-DD") →   end_date           (normalised to YYYY-MM-DD)
 *   status:
 *     'Completed'           →   service_status = 'completed'
 *     'Active'              →   service_status = 'in_progress'
 *   hire_resource_status:
 *     5                     →   service_type = 'proxy'
 *     6                     →   service_type = 'direct'
 *     anything else (NULL)  →   service_type = 'external'
 *
 * Duplicate detection:
 *   clients  → matched by client_name (case-insensitive)
 *   services → matched by (client_id, service_id, working resource, month, year)
 *
 * Usage:
 *   php artisan clients:migrate-old            # migrate, skip duplicates
 *   php artisan clients:migrate-old --dry-run  # preview only
 */
class MigrateClients extends Command
{
    protected $signature = 'clients:migrate-old
                            {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Migrate clients and client_resources from old DB to new DB.';

    private bool $isDryRun;

    /** old_user_id => new_user_id */
    private array $userIdMap = [];

    /** old_client_id => new_client_id */
    private array $clientIdMap = [];

    private array $stats = [
        'clients'  => ['inserted' => 0, 'skipped' => 0],
        'services' => ['inserted' => 0, 'skippedNoClient' => 0, 'skippedNoUser' => 0, 'skippedDuplicate' => 0],
    ];

    // ── Entry point ──────────────────────────────────────────────────────────

    public function handle(): int
    {
        $this->isDryRun = (bool) $this->option('dry-run');

        if ($this->isDryRun) {
            $this->warn('--- DRY RUN: no changes will be saved ---');
        }

        $this->buildUserIdMap();
        $this->migrateClients();
        $this->migrateServices();
        $this->printSummary();

        return self::SUCCESS;
    }

    // ── Build user map ───────────────────────────────────────────────────────

    private function buildUserIdMap(): void
    {
        $this->info('Building user ID map (old => new) by email...');

        $oldUsers = DB::connection('old_db')->table('users')->select('id', 'email')->get();

        $newUsers = DB::connection('mysql')
            ->table('users')
            ->select('id', 'email')
            ->get()
            ->keyBy(fn($u) => strtolower(trim($u->email)));

        foreach ($oldUsers as $old) {
            $key = strtolower(trim($old->email));
            if (isset($newUsers[$key])) {
                $this->userIdMap[$old->id] = $newUsers[$key]->id;
            }
        }

        $this->info('  Mapped ' . count($this->userIdMap) . ' of ' . $oldUsers->count() . ' old users.');
    }

    // ── Migrate clients ──────────────────────────────────────────────────────

    private function migrateClients(): void
    {
        $this->info("\nMigrating: clients (old) => clients (new)");

        // Index existing new-DB clients by lowercase name for duplicate detection
        $existingByName = DB::connection('mysql')
            ->table('clients')
            ->get()
            ->keyBy(fn($c) => strtolower(trim($c->name)));

        $oldClients = DB::connection('old_db')->table('clients')->orderBy('id')->get();

        foreach ($oldClients as $old) {
            $nameKey = strtolower(trim($old->client_name));

            // Already exists — record the mapping and move on
            if (isset($existingByName[$nameKey])) {
                $existingId = $existingByName[$nameKey]->id;
                $this->clientIdMap[$old->id] = $existingId;
                $this->line("  [SKIP - exists]  \"{$old->client_name}\" => new id={$existingId}");
                $this->stats['clients']['skipped']++;
                continue;
            }

            $newRow = [
                'user_id'      => 1,
                'name'         => $old->client_name,
                'client_email' => $old->client_email ?: null,
                'client_code'  => $old->client_code  ?: null,
                'start_date'   => $this->parseDate($old->starting_date),
                'end_date'     => $this->parseDate($old->end_date),
                'status'       => $this->mapClientStatus((int) $old->client_type_id),
                'client_type'  => $old->is_external ? 'external' : 'internal',
                'description'  => null,
                'budget'       => null,
                'project_name' => null,
                'type'         => null,
                'deleted_at'   => null,
                'created_at'   => $old->created_at,
                'updated_at'   => $old->updated_at,
            ];

            $this->line(
                "  [INSERT]  \"{$old->client_name}\"" .
                    "  status={$newRow['status']}" .
                    "  type={$newRow['client_type']}"
            );

            if (! $this->isDryRun) {
                $newId = DB::connection('mysql')->table('clients')->insertGetId($newRow);
                $this->clientIdMap[$old->id] = $newId;
                // Register in local index so later duplicates (same name) are caught
                $existingByName[$nameKey] = (object)['id' => $newId, 'name' => $old->client_name];
            } else {
                $this->clientIdMap[$old->id] = 'DRY:' . $old->id;
            }

            $this->stats['clients']['inserted']++;
        }
    }

    // ── Migrate services ─────────────────────────────────────────────────────

    private function migrateServices(): void
    {
        $this->info("\nMigrating: client_resources => services");

        $rows = DB::connection('old_db')->table('client_resources')->orderBy('id')->get();

        foreach ($rows as $row) {

            // ── Resolve client ────────────────────────────────────────────
            $newClientId = $this->clientIdMap[$row->client_id] ?? null;

            if ($newClientId === null) {
                $this->line("  [SKIP - no client]  id={$row->id}  old_client_id={$row->client_id}");
                $this->stats['services']['skippedNoClient']++;
                continue;
            }

            // In dry-run the client ID is a placeholder string; skip DB checks
            $isDryRunClient = is_string($newClientId) && str_starts_with($newClientId, 'DRY:');

            // ── Resolve users ─────────────────────────────────────────────
            $newWorkingUserId = $this->userIdMap[$row->working_user_id] ?? null;
            $newFrontUserId   = $this->userIdMap[$row->hire_user_id]    ?? null;

            if ($newWorkingUserId === null || $newFrontUserId === null) {
                $missingWorking = $newWorkingUserId === null ? "(working={$row->working_user_id} NOT FOUND)" : '';
                $missingFront   = $newFrontUserId   === null ? "(hire={$row->hire_user_id} NOT FOUND)"    : '';
                $this->line("  [SKIP - no user]  id={$row->id}  {$missingWorking} {$missingFront}");
                $this->stats['services']['skippedNoUser']++;
                continue;
            }

            $monthNum     = $this->monthNameToNumber($row->month);
            $serviceType  = $this->mapServiceType($row->hire_resource_status);
            $serviceStatus = $this->mapServiceStatus($row->status);

            // ── Duplicate check (real run only) ────────────────────────────
            if (! $this->isDryRun && ! $isDryRunClient) {
                $exists = DB::connection('mysql')
                    ->table('services')
                    ->where('client_id',        $newClientId)
                    ->where('service_id',       $row->service_id)
                    ->where('month',            $monthNum)
                    ->where('year',             $row->year ? (int) $row->year : null)
                    ->whereRaw('FIND_IN_SET(?, working_resource)', [$newWorkingUserId])
                    ->exists();

                if ($exists) {
                    $this->line("  [SKIP - duplicate]  client_resources.id={$row->id}");
                    $this->stats['services']['skippedDuplicate']++;
                    continue;
                }
            }

            $payload = [
                'client_id'          => $isDryRunClient ? null : $newClientId,
                'service_id'         => $row->service_id ?: null,
                'hours'              => $row->hours,
                'month'              => $monthNum,
                'year'               => $row->year  ? (int) $row->year  : null,
                'start_date'         => $this->parseShortDate($row->start_date),
                'end_date'           => $this->parseShortDate($row->end_date),
                'service_type'       => $serviceType,
                'service_status'     => $serviceStatus,
                'working_resource'   => (string) $newWorkingUserId,
                'front_resource'     => $newFrontUserId,
                'cancel_reason'      => null,
                'replacement_reason' => null,
                'deleted_at'         => null,
                'created_at'         => $row->created_at,
                'updated_at'         => $row->updated_at,
            ];

            $this->line(
                "  [INSERT]  id={$row->id}" .
                    "  type={$serviceType}" .
                    "  status={$serviceStatus}" .
                    "  hours={$row->hours}" .
                    "  month={$monthNum}/{$row->year}"
            );

            if (! $this->isDryRun) {
                DB::connection('mysql')->table('services')->insert($payload);
            }

            $this->stats['services']['inserted']++;
        }
    }

    // ── Mapping helpers ──────────────────────────────────────────────────────

    /**
     * client_type_id => clients.status
     *   1 = in_progress   2 = not_started   3 = completed
     */
    private function mapClientStatus(int $typeId): string
    {
        return match ($typeId) {
            1       => 'in_progress',
            2       => 'not_started',
            3       => 'completed',
            default => 'not_started',
        };
    }

    /**
     * hire_resource_status => services.service_type
     *   5 = proxy   6 = direct   anything else / NULL = external
     */
    private function mapServiceType(int|null $status): string
    {
        return match ((int) $status) {
            5       => 'proxy',
            6       => 'direct',
            default => 'external',
        };
    }

    /**
     * client_resources.status => services.service_status
     *   'Completed' => 'completed'
     *   'Active'    => 'in_progress'
     */
    private function mapServiceStatus(?string $status): string
    {
        return match (strtolower(trim((string) $status))) {
            'completed' => 'completed',
            'active'    => 'in_progress',
            default     => 'in_progress',
        };
    }

    /**
     * Month name → numeric month (1–12). "January" → 1, "May" → 5, etc.
     */
    private function monthNameToNumber(?string $month): ?int
    {
        if (blank($month)) {
            return null;
        }
        try {
            return (int) Carbon::parse('1 ' . trim($month) . ' 2000')->format('n');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Parse standard date strings (YYYY-MM-DD). Returns null on failure.
     */
    private function parseDate(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }
        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Parse the old DB's compact date format "YY-MM-DD" → "20YY-MM-DD".
     * e.g.  "22-03-14" → "2022-03-14"
     *       "25-01-06" → "2025-01-06"
     * Handles already-full YYYY-MM-DD dates too. Returns null on blank / bad input.
     */
    private function parseShortDate(?string $value): ?string
    {
        if (blank($value) || strtoupper($value) === 'NULL') {
            return null;
        }

        $value = trim($value);

        // Already full format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        // Short YY-MM-DD
        if (preg_match('/^(\d{2})-(\d{2})-(\d{2})$/', $value, $m)) {
            [$_, $yy, $mm, $dd] = $m;
            if ((int)$mm >= 1 && (int)$mm <= 12 && (int)$dd >= 1 && (int)$dd <= 31) {
                return '20' . $yy . '-' . $mm . '-' . $dd;
            }
        }

        // Fallback
        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    // ── Summary ──────────────────────────────────────────────────────────────

    private function printSummary(): void
    {
        $this->newLine();
        $this->info('Migration complete' . ($this->isDryRun ? ' (dry run)' : '') . ':');

        $this->info("\nClients:");
        $this->table(
            ['Action', 'Count'],
            [
                ['Inserted',                  $this->stats['clients']['inserted']],
                ['Skipped (already exists)',   $this->stats['clients']['skipped']],
            ]
        );

        $this->info("\nServices:");
        $this->table(
            ['Action', 'Count'],
            [
                ['Inserted',                      $this->stats['services']['inserted']],
                ['Skipped (client not found)',     $this->stats['services']['skippedNoClient']],
                ['Skipped (user not found)',       $this->stats['services']['skippedNoUser']],
                ['Skipped (duplicate)',            $this->stats['services']['skippedDuplicate']],
            ]
        );
    }
}
