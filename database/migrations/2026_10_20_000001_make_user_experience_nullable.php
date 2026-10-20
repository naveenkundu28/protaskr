<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('experience', 4, 1)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('users')->whereNull('experience')->update(['experience' => 0]);

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('experience', 4, 1)->nullable(false)->change();
        });
    }
};
