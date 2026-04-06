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
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('email_updated_by')->nullable();
            $table->foreign('email_updated_by')
              ->references('id')
              ->on('users')
              ->nullOnDelete();
            $table->timestamp('email_updated_at')->nullable();

            $table->enum('type', ['internal_team', 'project', 'client'])
                ->nullable();
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['email_updated_by']);
            $table->dropColumn([
                'email_updated_by',
                'email_updated_at',
                'type'
            ]);
        });
    }
};
