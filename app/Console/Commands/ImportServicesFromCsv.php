<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Support\Roles;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * ONE-TIME import of the monthly service sheets in files/services/*.csv
 * into the services table. Delete this file once the import is done.
 *
 * Rules (as agreed):
 *  - oct-26.csv is the reference: its service ids are kept. In every older file a
 *    service id that is already taken (by a later month, or missing) gets a new
 *    unique id in the same pattern: SERV + ddmmyy (1st of that month) + 3-digit counter.
 *  - Front resource = EmpID column. When the Resource Name holds several people
 *    ("Naveen (84) + Moiz (100)") the first one is the front resource, the service
 *    type is forced to proxy, every person goes into working_resource and the
 *    comment (services.cancel_reason, as on the bulk page) becomes "TK10401-84, TK12054-100".
 *  - Clients are matched by client email (name breaks ties between clients that share
 *    an email), otherwise by name; a missing client is created.
 *  - An employee that does not exist in users is created as an External user with a
 *    dummy email (they never log in).
 *  - A sheet row that already exists in the database (same client + front resource +
 *    month) is left alone.
 */
class ImportServicesFromCsv extends Command
{
    protected $signature = 'services:import-csv
                            {--dry-run : Preview everything without writing to the database}
                            {--path=files/services : Folder (relative to the project root) holding the month CSVs}
                            {--resources=files/resources.csv : Employee list (relative to the project root)}';

    protected $description = 'One-time import of the monthly service CSV sheets (files/services) into the database.';

    private const YEAR_SUFFIX_BASE = 2000;

    /** csv file prefix => month number. Processed newest first so the newest sheet owns its ids. */
    private const MONTHS = ['oct' => 10, 'sept' => 9, 'aug' => 8, 'july' => 7, 'jun' => 6, 'may' => 5, 'apr' => 4];

    /** Sheet first-names that can't be found by a plain name search => employee id. */
    private const NAME_ALIASES = [
        'jitender' => 'TK10829',
        'aarvind' => 'TK11649',
        'dipender' => 'TK11063',
        'deepak' => 'TK12195',
        'deepak hs' => 'TK12195',
        'vrushub' => 'TK12058',
        'anshul sir' => 'TK10268',
        'mayank dhingra' => 'EX01',
    ];

    private bool $dry;

    /** employee_id (upper) => User */
    private array $usersByCode = [];

    /** @var \Illuminate\Support\Collection<int, User> */
    private $users;

    /** employee_id => csv row of resources.csv */
    private array $resourceSheet = [];

    private $clients;

    /** service ids already handed out by this run (newest sheet first) */
    private array $takenIds = [];

    /** service ids that exist in the database (never generate one of these) */
    private array $dbIds = [];

    /** existing services (month-year => rows) not yet matched to a sheet row */
    private array $unclaimed = [];

    private array $plannedUsers = [];   // key => ['name','employee_id','email']
    private array $plannedClients = []; // key => ['name','email']
    private array $problems = [];
    private array $warnings = [];
    private array $plan = [];
    private array $tables = [];   // month => ['title','rows'], printed oldest first
    private int $nextExternalNumber = 1;

    public function handle(): int
    {
        $this->dry = (bool) $this->option('dry-run');
        $this->dry ? $this->warn('--- DRY RUN: nothing will be written ---') : $this->warn('--- LIVE RUN ---');

        $this->loadReferenceData();

        foreach (self::MONTHS as $prefix => $month) {
            $file = base_path(trim($this->option('path'), '/\\')) . DIRECTORY_SEPARATOR . $prefix . '-26.csv';
            if (!is_file($file)) {
                $this->warn("Missing file {$prefix}-26.csv, skipped.");
                continue;
            }
            $this->planFile($file, $prefix . '-26.csv', $month, 2026);
        }

        $this->report();

        if ($this->dry) {
            return self::SUCCESS;
        }

        if ($this->problems) {
            $this->error('Not importing: fix the problems listed above first (run with --dry-run to re-check).');
            return self::FAILURE;
        }

        DB::transaction(fn () => $this->commit());
        $this->info('Import finished.');

        return self::SUCCESS;
    }

    // ── reference data ───────────────────────────────────────────────────────

    private function loadReferenceData(): void
    {
        $this->users = User::withTrashed()->get();
        foreach ($this->users as $user) {
            if ($user->employee_id) {
                $this->usersByCode[strtoupper(trim($user->employee_id))] = $user;
            }
            if (preg_match('/^EX(\d+)$/i', (string) $user->employee_id, $m)) {
                $this->nextExternalNumber = max($this->nextExternalNumber, (int) $m[1] + 1);
            }
        }

        $this->clients = Client::withTrashed()->get();

        foreach ($this->readCsv(base_path(trim($this->option('resources'), '/\\'))) as $row) {
            $code = strtoupper(preg_replace('/\s+/', '', $row[4] ?? ''));
            if ($code !== '' && preg_match('/^[A-Z]{2}\d+$/', $code)) {
                $this->resourceSheet[$code] = $row;
            }
        }

        $this->dbIds = Service::withTrashed()->pluck('service_id')->filter()->flip()->all();

        foreach (Service::all() as $service) {
            $this->unclaimed["{$service->year}-{$service->month}"][] = $service;
        }
    }

    // ── planning ─────────────────────────────────────────────────────────────

    private function planFile(string $file, string $label, int $month, int $year): void
    {
        $counter = 0;
        $rows = [];

        foreach ($this->readCsv($file, true) as $line => $r) {
            // Only real data rows: the sheets also hold totals and skill lists below the table.
            if (!ctype_digit(trim($r[0] ?? ''))) {
                continue;
            }

            $where = "{$label} line {$line}";
            $empCode = strtoupper(preg_replace('/\s+/', '', $r[1] ?? ''));
            $statusText = strtolower(trim($r[2] ?? ''));
            $resourceName = trim(preg_replace('/\s+/', ' ', $r[3] ?? ''));
            $csvServiceId = trim($r[4] ?? '');
            $clientEmail = trim($r[5] ?? '');
            $clientName = trim(preg_replace('/\s+/', ' ', $r[6] ?? ''));
            $hours = is_numeric(trim($r[9] ?? '')) ? (float) trim($r[9]) : 0;
            $startDate = $this->parseDate($r[10] ?? '');

            // ── resources ────────────────────────────────────────────────
            $people = $this->splitResources($resourceName);
            $multi = count($people) > 1;
            $resolved = [];

            if ($multi) {
                foreach ($people as $p) {
                    $resolved[] = $this->resolvePerson($p['name'], null, $where, $p['hours']);
                }
            } else {
                $resolved[] = $this->resolvePerson($people[0]['name'] ?? $resourceName, $empCode ?: null, $where, null);
            }

            if (in_array(null, $resolved, true) || !$resolved) {
                $this->problems[] = "{$where}: could not resolve resource \"{$resourceName}\".";
                $rows[] = [count($rows) + 1, $this->short($clientName, 22), '-', $csvServiceId, $this->fmt($hours), '-', '?? ' . $this->short($resourceName, 20), '-', 'UNRESOLVED'];
                continue;
            }

            if ($multi && $empCode && strtoupper($resolved[0]['code']) !== $empCode) {
                $this->warnings[] = "{$where}: EmpID {$empCode} differs from first name \"{$people[0]['name']}\" ({$resolved[0]['code']}); using the name.";
            } elseif (!$multi && $empCode && strtoupper($resolved[0]['code']) !== $empCode) {
                $this->warnings[] = "{$where}: EmpID {$empCode} differs from name \"{$resourceName}\" ({$resolved[0]['code']}); using the name.";
            }

            // ── type / status ────────────────────────────────────────────
            $type = $multi ? 'proxy' : (str_contains($statusText, 'direct') ? 'direct' : (str_contains($statusText, 'proxy') ? 'proxy' : 'external'));
            $status = $startDate && $startDate->month === $month && $startDate->year === $year ? 'new' : 'in_progress';

            $comment = null;
            if ($multi) {
                $comment = collect($resolved)
                    ->map(fn ($p) => $p['code'] . '-' . $this->fmt($p['hours']))
                    ->implode(', ');
            }

            // ── client ───────────────────────────────────────────────────
            $client = $this->resolveClient($clientEmail, $clientName, $where);
            if ($client === false) {
                $rows[] = [count($rows) + 1, '?? ' . $this->short($clientName, 19), $type, $csvServiceId, $this->fmt($hours), $this->statusLabel($status), $this->personLabel($resolved[0]), '-', $comment ?: 'CLIENT UNRESOLVED'];
                continue;
            }

            $working = $type === 'proxy' ? collect($resolved)->map(fn ($p) => $this->firstName($p['name']))->implode(', ') : '-';
            $clientLabel = preg_replace('/^#[0-9]+ /', '', $client['label']);

            // ── does the database already hold this row? ─────────────────
            $existing = $this->claimExisting($client, $resolved[0], $month, $year);
            if ($existing) {
                $shownId = $existing->service_id;
                if (isset($this->takenIds[$existing->service_id])) {
                    // Same id as a newer sheet row => keep the row, give it a unique id.
                    $new = $this->nextServiceId($month, $year, $counter);
                    $this->plan[] = ['action' => 'renumber', 'service' => $existing, 'service_id' => $new];
                    $this->takenIds[$new] = true;
                    $shownId = $new;
                } else {
                    $this->takenIds[$existing->service_id] = true;
                    $this->plan[] = ['action' => 'skip'];
                }
                $rows[] = [count($rows) + 1, $this->short($clientLabel, 22), $type, $shownId, $this->fmt($hours), $this->statusLabel($status), $this->personLabel($resolved[0]), $working, $comment];
                continue;
            }

            // ── service id ───────────────────────────────────────────────
            $serviceId = $csvServiceId;
            if ($serviceId === '' || isset($this->takenIds[$serviceId])) {
                $serviceId = $this->nextServiceId($month, $year, $counter);
            }
            $this->takenIds[$serviceId] = true;

            $this->plan[] = [
                'action' => 'insert',
                'where' => $where,
                'client' => $client,
                'service_id' => $serviceId,
                'service_type' => $type,
                'hours' => $hours,
                'month' => $month,
                'year' => $year,
                'status' => $status,
                'start_date' => $startDate?->toDateString(),
                'people' => $resolved,
                'comment' => $comment,
            ];

            $rows[] = [count($rows) + 1, $this->short($clientLabel, 22), $type, $serviceId, $this->fmt($hours), $this->statusLabel($status), $this->personLabel($resolved[0]), $working, $comment];
        }

        $this->tables[$month] = ['title' => Carbon::create($year, $month, 1)->format('F Y') . "  ({$label})", 'rows' => $rows];
    }

    private function personLabel(array $person): string
    {
        return $this->firstName($person['name']) . " ({$person['code']})";
    }

    private function firstName(string $name): string
    {
        return Str::before(trim($name), ' ');
    }

    private function statusLabel(string $status): string
    {
        return $status === 'in_progress' ? 'in_prog' : $status;
    }

    private function short(string $text, int $max): string
    {
        return Str::limit($text, $max, '..');
    }

    /** "Naveen (84) + Moiz (100)" => [['name' => 'Naveen', 'hours' => 84], ...] */
    private function splitResources(string $text): array
    {
        $out = [];
        foreach (explode('+', $text) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $hours = null;
            if (preg_match('/\(\s*(\d+(?:\.\d+)?)\s*\)/', $part, $m)) {
                $hours = (float) $m[1];
                $part = trim(str_replace($m[0], '', $part));
            }
            // "Dev from another team" is not a resource we track.
            if (preg_match('/\b(?:team|another|other)\b/i', $part)) {
                continue;
            }
            $out[] = ['name' => $part, 'hours' => $hours];
        }

        return $out ?: [['name' => $text, 'hours' => null]];
    }

    /**
     * @return array{code:string,user:?User,key:?string,name:string,hours:?float}|null
     */
    private function resolvePerson(string $name, ?string $empCode, string $where, ?float $hours): ?array
    {
        $user = null;

        // The name decides who it is; the sheet's EmpID is only a fallback (it is often wrong).
        $user = $this->findUserByName($name);
        if (!$user && $empCode) {
            $user = $this->usersByCode[$empCode] ?? null;
        }

        if ($user) {
            return ['code' => $user->employee_id, 'user' => $user, 'key' => null, 'name' => $user->name, 'hours' => $hours];
        }

        // Not a known user => external resource.
        if ($name === '' || preg_match('/\b(team|another|other)\b/i', $name)) {
            return null;
        }

        $code = $empCode ?: null;
        $key = strtolower($code ?: $name);
        if (!isset($this->plannedUsers[$key])) {
            $code ??= sprintf('EX%02d', $this->nextExternalNumber++);
            $this->plannedUsers[$key] = [
                'name' => $name,
                'employee_id' => $code,
                'email' => Str::slug($name, '.') . '@noemail.local',
            ];
            $this->warnings[] = "{$where}: \"{$name}\" ({$code}) is not in users => will be created as External.";
        }

        return ['code' => $this->plannedUsers[$key]['employee_id'], 'user' => null, 'key' => $key, 'name' => $name, 'hours' => $hours];
    }

    private function findUserByName(string $name): ?User
    {
        $needle = strtolower(trim($name));
        if ($needle === '') {
            return null;
        }

        if (isset(self::NAME_ALIASES[$needle])) {
            return $this->usersByCode[self::NAME_ALIASES[$needle]] ?? null;
        }

        $exact = $this->users->filter(fn (User $u) => strtolower(trim($u->name)) === $needle);
        if ($exact->count() === 1) {
            return $exact->first();
        }

        // "Naveen" => "Naveen Kundu": the sheet name is the first word(s) of the full name.
        $prefix = $this->users->filter(fn (User $u) => str_starts_with(strtolower(trim($u->name)) . ' ', $needle . ' '));
        if ($prefix->count() === 1) {
            return $prefix->first();
        }

        $like = $this->users->filter(fn (User $u) => str_starts_with(strtolower(trim($u->name)), $needle));

        return $like->count() === 1 ? $like->first() : null;
    }

    /**
     * @return array{id:?int,key:?string,label:string}|false
     */
    private function resolveClient(string $emailCell, string $name, string $where)
    {
        $emails = collect(preg_split('#[/,;\s]+#', $emailCell))->map(fn ($e) => strtolower(trim($e)))->filter()->values();
        $plain = $this->plainName($name);

        $pickByName = function ($candidates) use ($plain) {
            if ($candidates->count() === 1) {
                return $candidates->first();
            }
            if ($plain === '') {
                return null;
            }
            $exact = $candidates->filter(fn (Client $c) => in_array($plain, [$this->plainName($c->name), $this->plainName($c->company_name)], true));
            if ($exact->count() === 1) {
                return $exact->first();
            }
            $near = $candidates->filter(function (Client $c) use ($plain) {
                foreach ([$this->plainName($c->name), $this->plainName($c->company_name)] as $n) {
                    if ($n !== '' && (str_contains($plain, $n) || str_contains($n, $plain))) {
                        return true;
                    }
                }
                return false;
            });

            return $near->count() === 1 ? $near->first() : null;
        };

        foreach ($emails as $email) {
            $matches = $this->clients->filter(fn (Client $c) => strtolower(trim((string) $c->client_email)) === $email);
            if ($matches->isEmpty()) {
                continue;
            }
            $client = $pickByName($matches);
            if ($client) {
                return ['id' => $client->id, 'key' => null, 'label' => "#{$client->id} {$client->name}"];
            }
            $this->problems[] = "{$where}: email {$email} is shared by clients [" . $matches->map(fn ($c) => "#{$c->id} {$c->name}")->implode(', ') . "] and \"{$name}\" matches none/more than one.";

            return false;
        }

        // No client with that email: fall back to the name before creating a new client.
        if ($plain !== '') {
            $byName = $pickByName($this->clients);
            if ($byName) {
                if ($emails->count()) {
                    $this->warnings[] = "{$where}: email {$emails->first()} not found, matched client \"{$name}\" by name to #{$byName->id} {$byName->name}.";
                }

                return ['id' => $byName->id, 'key' => null, 'label' => "#{$byName->id} {$byName->name}"];
            }
        }

        $key = strtolower($emails->first() ?: $name);
        if ($key === '') {
            $this->problems[] = "{$where}: no client email and no client name.";

            return false;
        }
        if (!isset($this->plannedClients[$key])) {
            $this->plannedClients[$key] = ['name' => $name ?: $emails->first(), 'email' => $emails->first()];
            $this->warnings[] = "{$where}: client \"{$name}\" ({$emails->first()}) not found => will be created.";
        }

        return ['id' => null, 'key' => $key, 'label' => "NEW {$this->plannedClients[$key]['name']}"];
    }

    private function plainName(?string $name): string
    {
        return trim(preg_replace('/\s+/', ' ', strtolower(preg_replace('/\(.*?\)/', '', (string) $name))));
    }

    /** Pairs a sheet row with an existing services row (same client, front resource, month). */
    private function claimExisting(array $client, array $front, int $month, int $year): ?Service
    {
        if (!$client['id'] || !$front['user']) {
            return null;
        }

        $bucket = &$this->unclaimed["{$year}-{$month}"];
        foreach ($bucket ?? [] as $i => $service) {
            if ((int) $service->client_id === (int) $client['id'] && (int) $service->front_resource === (int) $front['user']->id) {
                unset($bucket[$i]);

                return $service;
            }
        }

        return null;
    }

    private function nextServiceId(int $month, int $year, int &$counter): string
    {
        $prefix = 'SERV' . sprintf('01%02d%02d', $month, $year - self::YEAR_SUFFIX_BASE);
        do {
            $id = $prefix . sprintf('%03d', ++$counter);
        } while (isset($this->takenIds[$id]) || isset($this->dbIds[$id]));

        return $id;
    }

    // ── report / commit ──────────────────────────────────────────────────────

    private function report(): void
    {
        $insert = collect($this->plan)->where('action', 'insert')->count();
        $skip = collect($this->plan)->where('action', 'skip')->count();
        $renumber = collect($this->plan)->where('action', 'renumber')->count();

        ksort($this->tables);
        foreach ($this->tables as $t) {
            $this->info("\n== {$t['title']}");
            $this->table(
                ['#', 'Client', 'Type', 'Service ID', 'Hrs', 'Status', 'Front', 'Working', 'Comment'],
                $t['rows']
            );
        }

        $this->newLine();
        foreach ($this->plannedUsers as $u) {
            $this->line("  [NEW USER]   {$u['employee_id']}  {$u['name']}  {$u['email']}  (External)");
        }
        foreach ($this->plannedClients as $c) {
            $this->line("  [NEW CLIENT] {$c['name']}  " . ($c['email'] ?: '(no email)'));
        }

        if ($this->warnings) {
            $this->newLine();
            $this->warn('WARNINGS (' . count($this->warnings) . ')');
            foreach (array_unique($this->warnings) as $w) {
                $this->line("  - {$w}");
            }
        }
        if ($this->problems) {
            $this->newLine();
            $this->error('PROBLEMS - these rows cannot be imported (' . count($this->problems) . ')');
            foreach ($this->problems as $p) {
                $this->line("  - {$p}");
            }
        }

        $this->newLine();
        $this->info("Services to insert: {$insert} | already in database (skipped): {$skip} | existing rows renumbered: {$renumber} | new users: " . count($this->plannedUsers) . ' | new clients: ' . count($this->plannedClients) . ' | problems: ' . count($this->problems));
    }

    private function commit(): void
    {
        $roleId = Role::where('name', Roles::RESOURCE)->value('id');

        $userIds = [];
        foreach ($this->plannedUsers as $key => $u) {
            $user = User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => Hash::make(Str::random(40)),
                'user_type' => 'External',
                'employee_id' => $u['employee_id'],
                'role_id' => $roleId,
            ]);
            $userIds[$key] = $user->id;
        }

        $clientIds = [];
        foreach ($this->plannedClients as $key => $c) {
            $clientIds[$key] = Client::create([
                'user_id' => 1,
                'name' => $c['name'],
                'company_name' => $c['name'],
                'client_email' => $c['email'],
                'status' => 'in_progress',
                'type' => 'client',
            ])->id;
        }

        foreach ($this->plan as $p) {
            if ($p['action'] === 'renumber') {
                $p['service']->update(['service_id' => $p['service_id']]);
                continue;
            }
            if ($p['action'] !== 'insert') {
                continue;
            }

            $ids = collect($p['people'])->map(fn ($x) => $x['user']?->id ?? $userIds[$x['key']])->all();
            $clientId = $p['client']['id'] ?? $clientIds[$p['client']['key']];

            Service::create([
                'client_id' => $clientId,
                'service_type' => $p['service_type'],
                'service_id' => $p['service_id'],
                'hours' => $p['hours'],
                'month' => $p['month'],
                'year' => $p['year'],
                'service_status' => $p['status'],
                'front_resource' => $ids[0],
                'working_resource' => $ids,
                'start_date' => $p['start_date'],
                'end_date' => null,
                'cancel_reason' => $p['comment'],
                'replacement_reason' => null,
            ]);

            Client::find($clientId)?->teamProjects()->syncWithoutDetaching($ids);
        }
    }

    // ── small helpers ────────────────────────────────────────────────────────

    /** @return array<int, array<int, string>> rows keyed by their file line number (when $keepLines) */
    private function readCsv(string $file, bool $keepLines = false): array
    {
        $rows = [];
        if (!is_file($file) || ($h = fopen($file, 'r')) === false) {
            return $rows;
        }

        $line = 0;
        while (($row = fgetcsv($h)) !== false) {
            $line++;
            // The sheets are saved by Excel as Windows-1252, not UTF-8.
            $row = array_map(fn ($v) => mb_convert_encoding((string) $v, 'UTF-8', 'Windows-1252'), $row);
            if ($line === 1) {
                continue;
            }
            $rows[$line] = $row;
        }
        fclose($h);

        return $keepLines ? $rows : array_values($rows);
    }

    private function parseDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        // Sheets mix "5-Jul-21" and "5-Jul-2021"; a wrong format throws, so try each.
        // ("Y" would read "26" as the year 0026, so pick the format by the year's length.)
        $format = preg_match('/-[0-9]{4}$/', $value) ? 'j-M-Y' : 'j-M-y';
        try {
            return Carbon::createFromFormat($format, $value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fmt(?float $n): string
    {
        return $n === null ? '' : rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }
}
