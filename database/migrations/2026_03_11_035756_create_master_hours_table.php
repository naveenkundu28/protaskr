<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_hours', function (Blueprint $table) {
            $table->id();
            $table->year('year')->nullable();
            $table->string('month')->nullable();
            $table->integer('hours')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_hours');
    }
};