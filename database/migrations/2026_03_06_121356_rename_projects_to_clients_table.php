<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // rename table
        Schema::rename('projects', 'clients');

        // add new columns
        Schema::table('clients', function (Blueprint $table) {
            $table->string('client_email')->nullable();
            $table->string('client_code')->nullable();
            $table->string('project_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['client_email', 'client_code', 'project_name']);
        });

        Schema::rename('clients', 'projects');
    }
};