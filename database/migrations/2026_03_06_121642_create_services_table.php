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
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->enum('service_type', ['direct','proxy','external']);
            $table->foreignId('client_id')
                  ->nullable()
                  ->constrained('clients')
                  ->cascadeOnDelete();

            $table->string('service_id')->unique(); // not primary key

            $table->decimal('hours', 8, 2)->nullable();

            $table->enum('service_status', ['new','continuing','closed']);

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
