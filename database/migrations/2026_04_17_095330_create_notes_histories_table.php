<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('notes_histories', function (Blueprint $table) {
            $table->id();

            $table->longText('notes');

            $table->enum('status', [
                'email_inputs_shared',
                'in_call_discussion'
            ])->default('email_inputs_shared');

            $table->string('actions')->nullable();

            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes_histories');
    }
};
