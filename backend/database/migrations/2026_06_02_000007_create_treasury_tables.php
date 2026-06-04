<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('account_name');
            $table->string('account_number')->nullable();
            $table->string('bank_name');
            $table->string('currency', 10)->default('USD');
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->decimal('available_balance', 15, 2)->default(0);
            $table->string('status')->default('active')->index();
            $table->string('color_hex', 7)->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('bank_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->date('transaction_date')->index();
            $table->string('description');
            $table->decimal('amount', 15, 2); // Positive = credit, Negative = debit
            $table->string('type')->default('debit')->index(); // debit | credit
            $table->string('reference')->nullable();
            $table->string('reconciliation_status')->default('unreconciled')->index(); // unreconciled | reconciled | excluded
            $table->timestamps();
            $table->index(['tenant_id', 'bank_account_id', 'reconciliation_status']);
        });

        Schema::create('reconciliations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->date('statement_date');
            $table->decimal('statement_closing_balance', 15, 2);
            $table->string('status')->default('in_progress')->index(); // in_progress | completed
            $table->unsignedBigInteger('completed_by')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'bank_account_id', 'status']);
        });

        Schema::create('reconciliation_matches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reconciliation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_line_id')->nullable()->constrained()->nullOnDelete();
            $table->string('match_type')->default('manual'); // manual | ai_suggested
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_matches');
        Schema::dropIfExists('reconciliations');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_accounts');
    }
};
