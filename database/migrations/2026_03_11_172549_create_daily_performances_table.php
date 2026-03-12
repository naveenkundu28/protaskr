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
        Schema::create('daily_performances', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('background_color');
            $table->string('font_color');
            $table->boolean('need_a_reason')->default(0);
            $table->integer('min');
            $table->integer('max');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_performances');
    }
};
