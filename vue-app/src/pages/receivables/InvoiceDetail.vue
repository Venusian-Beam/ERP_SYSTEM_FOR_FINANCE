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
    const data = await receivablesService.invoice(route.params.id)
    const inv = data.record || data
    title.value = inv.number || inv.reference
    subtitle.value = `${inv.customer} · Customer invoice`
    status.value = inv.status
    amount.value = `GHC ${Number(inv.amount).toLocaleString()}`
    details.value = [
      { label: 'Customer', value: inv.customer },
      { label: 'Issue Date', value: inv.date },
      { label: 'Due Date', value: inv.due },
      { label: 'Payment Terms', value: inv.terms || 'Net 30' },
    ]
  } catch (e) {
    console.error('Failed to load invoice:', e)
  } finally {
    loading.value = false
  }
})
</script>
<template>
  <div v-if="loading" class="flex justify-center items-center h-64"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div></div>
  <FinanceDetailWorkspace v-else :title="title" :subtitle="subtitle" :status="status" amount-label="Amount Due" :amount="amount" :details="details" :lines="lines" :timeline="timeline" primary-action="Send Reminder" @primary-action="console.log('Send Reminder not implemented')" @download-pdf="console.log('Download PDF not implemented')" />
</template>
