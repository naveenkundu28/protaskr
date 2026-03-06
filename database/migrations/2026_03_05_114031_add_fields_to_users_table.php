<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type')->nullable()->after('email');
            $table->string('employee_id')->nullable()->after('user_type');
            $table->date('joining_date')->nullable()->after('employee_id');
            $table->string('phone_no')->nullable()->after('joining_date');
            $table->string('alt_phone_no')->nullable()->after('phone_no');
            $table->integer('experience')->nullable()->after('alt_phone_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'employee_id', 'joining_date', 'phone_no', 'alt_phone_no', 'experience']);
        });
    }
};
