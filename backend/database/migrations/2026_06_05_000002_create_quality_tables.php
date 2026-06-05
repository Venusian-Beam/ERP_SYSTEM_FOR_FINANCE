<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qa_tests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('test_type');
            $table->text('steps')->nullable();
            $table->text('expected_result')->nullable();
            $table->string('status')->default('pending')->index();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->string('priority')->default('medium')->index();
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'status']);
        });

        Schema::create('risks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('probability');
            $table->string('impact');
            $table->string('severity');
            $table->text('mitigation')->nullable();
            $table->text('contingency')->nullable();
            $table->string('status')->default('open')->index();
            $table->unsignedBigInteger('owner')->nullable()->index();
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'severity']);
        });

        Schema::create('change_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('change_type');
            $table->string('priority')->default('medium')->index();
            $table->string('status')->default('pending')->index();
            $table->unsignedBigInteger('requested_by')->nullable()->index();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_logs');
        Schema::dropIfExists('risks');
        Schema::dropIfExists('qa_tests');
    }
};
