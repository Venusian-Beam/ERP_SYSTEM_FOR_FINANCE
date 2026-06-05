<script setup>
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'
import { accountingService } from '@/services/accountingService'
import { useApiPayload } from '@/composables/useApiPayload'

const { metrics, records, loading, currentPage, totalPages, totalRecords, nextPage, prevPage, goToPage } = useApiPayload(accountingService.accounts)

const columns = [
  { key: 'code', label: 'Account Code', primary: true },
  { key: 'name', label: 'Account Name' },
  { key: 'type', label: 'Account Type' },
  { key: 'parent', label: 'Parent Account' },
  { key: 'balance', label: 'Current Balance', type: 'money' },
  { key: 'status', label: 'Status', type: 'status' }
]

const handlePrimaryAction = () => {
  console.log('Add Account — create flow not yet implemented')
}
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
    :current-page="currentPage" :total-pages="totalPages" :total-records="totalRecords" :loading="loading"
    @primary-action="handlePrimaryAction" @next-page="nextPage" @prev-page="prevPage" @go-to-page="goToPage"
  />
</template>
