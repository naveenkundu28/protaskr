<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('projects')) Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('project_type', 100)->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->decimal('budget_hours', 10, 2)->nullable();
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'completed', 'on_hold', 'not_started'])->default('not_started')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
        if (!Schema::hasTable('project_members')) Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });
        if (!Schema::hasTable('stories')) Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['to_do', 'in_progress', 'in_review', 'completed', 'blocked'])->default('to_do')->index();
            $table->date('due_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'project_id')) $table->unsignedBigInteger('project_id')->nullable()->after('client_id');
            if (!Schema::hasColumn('tasks', 'story_id')) $table->unsignedBigInteger('story_id')->nullable()->after('project_id');
            if (!Schema::hasColumn('tasks', 'estimated_hours')) $table->decimal('estimated_hours', 8, 2)->nullable()->after('due_date');
        });
        // Explicit names avoid the old legacy project_id constraint name.
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('project_id', 'tasks_delivery_project_id_foreign')->references('id')->on('projects')->nullOnDelete();
            $table->foreign('story_id', 'tasks_story_id_foreign')->references('id')->on('stories')->nullOnDelete();
            $table->index(['project_id', 'story_id'], 'tasks_delivery_project_story_index');
        });
    }
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) { $table->dropForeign('tasks_delivery_project_id_foreign'); $table->dropForeign('tasks_story_id_foreign'); $table->dropIndex('tasks_delivery_project_story_index'); $table->dropColumn(['project_id', 'story_id', 'estimated_hours']); });
        Schema::dropIfExists('stories');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
    }
};
