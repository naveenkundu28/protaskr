<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('stories', 'story_points')) {
            Schema::table('stories', function (Blueprint $table) {
                $table->unsignedSmallInteger('story_points')->nullable()->after('description');
            });
        }

        if (!Schema::hasColumn('stories', 'acceptance_criteria')) {
            Schema::table('stories', function (Blueprint $table) {
                $table->longText('acceptance_criteria')->nullable()->after('story_points');
            });
        }
    }

    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn(['story_points', 'acceptance_criteria']);
        });
    }
};
