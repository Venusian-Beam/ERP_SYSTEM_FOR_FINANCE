<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('tax_identifier')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->index(['tenant_id', 'name']);
        });

        Schema::create('supplier_invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('invoice_number');
            $table->date('invoice_date')->index();
            $table->date('due_date')->index();
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('pending_approval')->index();
            $table->string('supporting_document_path')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'supplier_id', 'invoice_number']);
        });

        Schema::create('approval_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_invoice_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->unsignedBigInteger('approver_id')->nullable()->index();
            $table->string('approver_role')->index();
            $table->string('status')->default('pending')->index();
            $table->timestamp('acted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'supplier_invoice_id', 'sequence']);
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->index(['tenant_id', 'name']);
        });

        Schema::create('customer_invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('invoice_number');
            $table->date('invoice_date')->index();
            $table->date('due_date')->index();
            $table->decimal('amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status')->default('issued')->index();
            $table->boolean('is_finalized')->default(false)->index();
            $table->timestamps();
            $table->unique(['tenant_id', 'customer_id', 'invoice_number']);
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_invoice_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamp('paid_at')->index();
            $table->string('reference');
            $table->string('method')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'reference']);
        });

        Schema::create('credit_debit_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->timestamp('issued_at')->index();
            $table->timestamps();
            $table->index(['tenant_id', 'customer_invoice_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_debit_notes');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('customer_invoices');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('supplier_invoices');
        Schema::dropIfExists('suppliers');
    }
};
