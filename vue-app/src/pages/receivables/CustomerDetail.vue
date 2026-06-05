<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import FinanceDetailWorkspace from '@/components/finance/FinanceDetailWorkspace.vue'
import { receivablesService } from '@/services/receivablesService'

const route = useRoute()
const details = ref([])
const lines = ref([])
const timeline = ref([])
const title = ref('')
const subtitle = ref('')
const status = ref('')
const amount = ref('')
const loading = ref(true)

onMounted(async () => {
  try {
    const data = await receivablesService.customer(route.params.id)
    const cust = data.record || data
    title.value = cust.name
    subtitle.value = 'Customer profile and receivables history'
    status.value = cust.status || 'Active'
    amount.value = cust.balance ? `GHC ${Number(cust.balance).toLocaleString()}` : 'GHC 0.00'
    details.value = [
      { label: 'Primary Contact', value: cust.email || '—' },
      { label: 'Payment Terms', value: cust.terms || 'Net 30' },
      { label: 'Credit Limit', value: cust.credit_limit ? `GHC ${Number(cust.credit_limit).toLocaleString()}` : '—' },
      { label: 'Lifetime Value', value: cust.ltv ? `GHC ${Number(cust.ltv).toLocaleString()}` : '—' },
    ]
  } catch (e) {
    console.error('Failed to load customer:', e)
  } finally {
    loading.value = false
  }
})
</script>
<template>
  <div v-if="loading" class="flex justify-center items-center h-64"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div></div>
  <FinanceDetailWorkspace v-else :title="title" :subtitle="subtitle" :status="status" amount-label="Open Balance" :amount="amount" :details="details" :lines="lines" :timeline="timeline" primary-action="Create Invoice" @primary-action="console.log('Create Invoice not implemented')" @download-pdf="console.log('Download PDF not implemented')" />
</template>
