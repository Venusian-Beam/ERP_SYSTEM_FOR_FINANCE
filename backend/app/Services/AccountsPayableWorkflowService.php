<?php

declare(strict_types=1);

namespace App\Services;

final class AccountsPayableWorkflowService
{
    /**
     * @return array<int, array{role: string, status: string}>
     */
    public function approvalStepsFor(float $amount): array
    {
        $tier1Limit = (float) config('erp.ap_workflow.tier_1_limit', 1000);
        $tier2Limit = (float) config('erp.ap_workflow.tier_2_limit', 10000);

        if ($amount < $tier1Limit) {
            return [['role' => 'finance_manager', 'status' => 'pending']];
        }

        if ($amount < $tier2Limit) {
            return [
                ['role' => 'finance_manager', 'status' => 'pending'],
                ['role' => 'tenant_admin', 'status' => 'waiting'],
            ];
        }

        return [
            ['role' => 'auditor', 'status' => 'pending'],
            ['role' => 'tenant_admin', 'status' => 'waiting'],
        ];
    }

    public function initialStatusFor(float $amount, bool $hasSupportingDocument): string
    {
        $tier2Limit = (float) config('erp.ap_workflow.tier_2_limit', 10000);

        if ($amount >= $tier2Limit && ! $hasSupportingDocument) {
            return 'blocked_supporting_document_required';
        }

        return 'pending_approval';
    }
}
