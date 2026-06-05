<script setup>
import FinancialReportWorkspace from '@/components/finance/FinancialReportWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import { reportsService } from '@/services/reportsService'

const { metrics, sections } = useApiPayload(reportsService.cashFlow)
</script>

<template>
  <FinancialReportWorkspace title="Cash Flow Statement" subtitle="Understand operating cash movement" :metrics="metrics" :sections="sections" :insight="{title:'Cash flow uses bank activity',text:'Inflows and outflows are calculated from backend bank transactions.'}" />
</template>
