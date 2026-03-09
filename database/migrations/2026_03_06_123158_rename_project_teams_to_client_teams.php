<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // rename table
        Schema::rename('project_teams', 'client_teams');

        // rename column using raw SQL
        DB::statement('ALTER TABLE client_teams CHANGE project_id client_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE client_teams CHANGE client_id project_id BIGINT UNSIGNED NOT NULL');

        Schema::rename('client_teams', 'project_teams');
    }
};