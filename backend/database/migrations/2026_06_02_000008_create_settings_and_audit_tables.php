<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('company_name');
            $table->string('registration_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('address')->nullable();
            $table->string('currency', 10)->default('USD');
            $table->string('fiscal_year_start', 5)->default('01-01'); // MM-DD
            $table->string('logo_path')->nullable();
            $table->json('notification_channels')->nullable(); // email, slack, whatsapp
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable(); // Array of permission slugs
            $table->boolean('is_system')->default(false); // System roles can't be deleted
            $table->timestamps();
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['tenant_id', 'user_id', 'role_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action'); // created | updated | deleted | approved | rejected
            $table->string('auditable_type')->index(); // Model class name
            $table->unsignedBigInteger('auditable_id')->index();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'auditable_type', 'auditable_id']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('company_settings');
    }
};
