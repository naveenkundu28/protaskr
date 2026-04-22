<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Create lead_statuses table
        Schema::create('lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });



        // Add lead_status_id column to notes_histories
        Schema::table('notes_histories', function (Blueprint $table) {
            $table->foreignId('lead_status_id')->nullable()->constrained('lead_statuses')->nullOnDelete();
        });


        // Drop enum column after migration
        Schema::table('notes_histories', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the enum column
        Schema::table('notes_histories', function (Blueprint $table) {
            $table->enum('status', [
                'email_inputs_shared',
                'in_call_discussion'
            ])->default('email_inputs_shared');
        });



        // Drop foreign key column
        Schema::table('notes_histories', function (Blueprint $table) {
            $table->dropForeign(['lead_status_id']); // drop FK constraint
            $table->dropColumn('lead_status_id');    // drop column
        });

        // Drop lead_statuses table
        Schema::dropIfExists('lead_statuses');
    }
};
