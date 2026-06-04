<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SupplierInvoice;
use Illuminate\Support\Facades\DB;

final class PayablesService
{
    public function __construct(
        private readonly AccountsPayableWorkflowService $workflow,
        private readonly RealTimeRuleEvaluator $rules,
    )
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createInvoice(array $payload): SupplierInvoice
    {
        return DB::transaction(function () use ($payload): SupplierInvoice {
            $hasSupportingDocument = filled($payload['supporting_document_path'] ?? null);
            $amount = (float) $payload['amount'];

            $invoice = SupplierInvoice::query()->create([
                'supplier_id' => $payload['supplier_id'],
                'invoice_number' => $payload['invoice_number'],
                'invoice_date' => $payload['invoice_date'],
                'due_date' => $payload['due_date'],
                'amount' => $amount,
                'supporting_document_path' => $payload['supporting_document_path'] ?? null,
                'status' => $this->workflow->initialStatusFor($amount, $hasSupportingDocument),
            ]);

            foreach ($this->workflow->approvalStepsFor($amount) as $index => $step) {
                $invoice->approvals()->create([
                    'sequence' => $index + 1,
                    'approver_id' => $payload['approver_ids'][$step['role']] ?? null,
                    'approver_role' => $step['role'],
                    'status' => $step['status'],
                ]);
            }

            $this->rules->evaluateSupplierInvoice($invoice);

            return $invoice->refresh()->load(['supplier', 'approvals']);
        });
    }
}
