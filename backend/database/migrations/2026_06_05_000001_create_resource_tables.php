<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('role');
            $table->decimal('hourly_rate', 10, 2);
            $table->string('avatar_url')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->index(['tenant_id', 'project_id']);
        });

        Schema::create('time_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('hours', 8, 2);
            $table->date('date');
            $table->boolean('billable')->default(false);
            $table->boolean('approved')->default(false);
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'date']);
        });

        Schema::create('milestones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->string('status')->default('pending')->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'status']);
        });

        Schema::create('budget_expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->boolean('approved')->default(false);
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_expenses');
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('team_members');
    }
};
