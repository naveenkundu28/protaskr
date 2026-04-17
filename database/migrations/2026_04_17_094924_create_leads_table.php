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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('lead');

            // Foreign keys
            $table->foreignId('tl')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('team')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('requirement_id')->nullable()->constrained('requirements')->nullOnDelete();

            $table->enum('claimed_group', ['chat', 'gl'])->nullable();

            $table->boolean('call_done')->default(false);

            $table->string('cam_member')->nullable();

            $table->date('date')->nullable();

            $table->longText('jd')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
