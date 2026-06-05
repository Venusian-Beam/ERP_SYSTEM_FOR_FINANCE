<script setup>
import FinancialReportWorkspace from '@/components/finance/FinancialReportWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import { reportsService } from '@/services/reportsService'

const { metrics, sections } = useApiPayload(reportsService.profitLoss)
</script>

<template>
  <FinancialReportWorkspace title="Profit and Loss" subtitle="Analyze revenue, expenses, and profitability" :metrics="metrics" :sections="sections" :insight="{title:'Report uses ledger balances',text:'Revenue, expenses, and net income are calculated from financial account balances.'}" />
</template>
