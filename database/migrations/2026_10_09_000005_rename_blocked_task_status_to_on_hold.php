<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tasks MODIFY status ENUM('to_do','in_progress','completed','blocked','on_hold','in_review','pending','closed') NOT NULL DEFAULT 'to_do'");
        DB::table('tasks')->where('status', 'blocked')->update(['status' => 'on_hold']);
        DB::statement("ALTER TABLE tasks MODIFY status ENUM('to_do','in_progress','completed','on_hold','in_review','pending','closed') NOT NULL DEFAULT 'to_do'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tasks MODIFY status ENUM('to_do','in_progress','completed','blocked','on_hold','in_review','pending','closed') NOT NULL DEFAULT 'to_do'");
        DB::table('tasks')->where('status', 'on_hold')->update(['status' => 'blocked']);
        DB::statement("ALTER TABLE tasks MODIFY status ENUM('to_do','in_progress','completed','blocked','in_review','pending','closed') NOT NULL DEFAULT 'to_do'");
    }
};
