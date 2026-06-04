<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'

const metrics = ref([])
const records = ref([])
const loading = ref(true)

const columns = [
  { key: 'code', label: 'Account Code', primary: true },
  { key: 'name', label: 'Account Name' },
  { key: 'type', label: 'Account Type' },
  { key: 'parent', label: 'Parent Account' },
  { key: 'balance', label: 'Current Balance', type: 'money' },
  { key: 'status', label: 'Status', type: 'status' }
]

onMounted(async () => {
  try {
    const response = await axios.get('/api/accounting/chart-of-accounts')
    metrics.value = response.data.metrics
    records.value = response.data.records
  } catch (error) {
    console.error('Failed to fetch chart of accounts:', error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div v-if="loading" class="flex justify-center items-center h-64">
    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
  </div>
  <FinanceListWorkspace 
    v-else
    title="Chart of Accounts" 
    subtitle="Manage the hierarchy and classification of general ledger accounts" 
    action-label="Add Account" 
    :metrics="metrics" 
    :columns="columns" 
    :records="records" 
    :filters="['Active', 'Header', 'Current Asset', 'Current Liability']" 
    :insight="{title:'Account structure looks healthy', text:'No duplicate account codes or posting activity against header accounts were detected.'}" 
  />
</template>
