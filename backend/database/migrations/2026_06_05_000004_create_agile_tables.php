<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sprints', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('goal')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('planning')->index();
            $table->decimal('velocity', 8, 2)->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'status']);
        });

        Schema::create('backlog_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sprint_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('story_points')->nullable();
            $table->string('priority')->default('medium')->index();
            $table->string('status')->default('backlog')->index();
            $table->string('type')->default('story');
            $table->unsignedBigInteger('assignee')->nullable()->index();
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'sprint_id']);
        });

        Schema::create('agile_definitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('definition_type');
            $table->json('content');
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
            $table->index(['tenant_id', 'definition_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agile_definitions');
        Schema::dropIfExists('backlog_items');
        Schema::dropIfExists('sprints');
    }
};
