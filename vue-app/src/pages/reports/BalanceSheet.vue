<script setup>
import FinancialReportWorkspace from '@/components/finance/FinancialReportWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import { reportsService } from '@/services/reportsService'

const { metrics, sections } = useApiPayload(reportsService.balanceSheet)
</script>

<template>
  <FinancialReportWorkspace title="Balance Sheet" subtitle="Review assets, liabilities, and equity" :metrics="metrics" :sections="sections" :insight="{title:'Balance sheet uses backend balances',text:'Assets, liabilities, and equity are grouped from the chart of accounts.'}" />
</template>
