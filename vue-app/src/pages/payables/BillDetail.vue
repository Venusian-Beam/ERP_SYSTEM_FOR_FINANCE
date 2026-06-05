<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import FinanceDetailWorkspace from '@/components/finance/FinanceDetailWorkspace.vue'
import { payablesService } from '@/services/payablesService'

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
    const data = await payablesService.bill(route.params.id)
    const bill = data.record || data
    title.value = bill.number || bill.reference
    subtitle.value = `${bill.vendor} · Vendor bill`
    status.value = bill.status
    amount.value = `GHC ${Number(bill.amount).toLocaleString()}`
    details.value = [
      { label: 'Vendor', value: bill.vendor },
      { label: 'Bill Date', value: bill.date },
      { label: 'Due Date', value: bill.due },
      { label: 'Payment Terms', value: bill.terms || 'Net 30' },
    ]
  } catch (e) {
    console.error('Failed to load bill:', e)
  } finally {
    loading.value = false
  }
})
</script>
<template>
  <div v-if="loading" class="flex justify-center items-center h-64"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div></div>
  <FinanceDetailWorkspace v-else :title="title" :subtitle="subtitle" :status="status" amount-label="Balance Due" :amount="amount" :details="details" :lines="lines" :timeline="timeline" primary-action="Approve & Pay" @primary-action="console.log('Approve & Pay not implemented')" @download-pdf="console.log('Download PDF not implemented')" />
</template>
