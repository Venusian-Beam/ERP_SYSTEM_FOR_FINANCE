<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stakeholders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('role');
            $table->string('influence')->default('medium');
            $table->string('interest')->default('medium');
            $table->text('expectations')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->index(['tenant_id', 'project_id']);
        });

        Schema::create('kickoffs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('meeting_date');
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('location')->nullable();
            $table->text('agenda')->nullable();
            $table->text('minutes')->nullable();
            $table->string('status')->default('scheduled')->index();
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kickoffs');
        Schema::dropIfExists('stakeholders');
    }
};
