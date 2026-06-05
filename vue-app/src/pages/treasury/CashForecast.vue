<script setup>
import FinancialReportWorkspace from '@/components/finance/FinancialReportWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import { treasuryService } from '@/services/treasuryService'

const { metrics, sections } = useApiPayload(treasuryService.cashForecast)
</script>

<template>
  <FinancialReportWorkspace title="Cash Forecast" subtitle="Predict liquidity across 30, 60, and 90-day horizons" period="Backend forecast" :metrics="metrics" :sections="sections" :insight="{title:'Forecast uses live finance records',text:'Cash, open receivables, and open payables are calculated by the backend.'}" />
</template>
