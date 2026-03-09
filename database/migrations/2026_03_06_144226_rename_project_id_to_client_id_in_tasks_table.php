<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        DB::statement('ALTER TABLE tasks CHANGE project_id client_id BIGINT UNSIGNED NOT NULL');

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        DB::statement('ALTER TABLE tasks CHANGE client_id project_id BIGINT UNSIGNED NOT NULL');

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('project_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('cascade');
        });
    }
};