<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('status')->default('in_progress')->index();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->decimal('budget_amount', 15, 2)->default(0);
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('project_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('status')->default('todo')->index();
            $table->string('priority')->default('normal')->index();
            $table->unsignedBigInteger('assignee_id')->nullable()->index();
            $table->date('due_date')->nullable()->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'status']);
        });

        Schema::create('relationship_edges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('parent_type');
            $table->unsignedBigInteger('parent_id');
            $table->string('child_type');
            $table->unsignedBigInteger('child_id');
            $table->string('relation');
            $table->timestamps();
            $table->index(['tenant_id', 'parent_type', 'parent_id']);
            $table->index(['tenant_id', 'child_type', 'child_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relationship_edges');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('projects');
    }
};
