<script setup>
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import { treasuryService } from '@/services/treasuryService'

const { metrics, records, currentPage, totalPages, totalRecords, loading, nextPage, prevPage, goToPage } = useApiPayload(treasuryService.reconciliation)
const columns = [
  { key: 'date', label: 'Bank Date', primary: true },
  { key: 'description', label: 'Bank Description' },
  { key: 'suggestion', label: 'Suggested Ledger Match' },
  { key: 'confidence', label: 'Confidence' },
  { key: 'amount', label: 'Amount', type: 'money' },
  { key: 'status', label: 'Status', type: 'status' },
]

const handlePrimaryAction = () => {
  console.log('Reconcile Selected — flow not yet implemented')
}

const handleEdit = (record) => {
  alert(`Edit reconciliation entry for ${record.description || record.date}`)
}

const handleDelete = (record) => {
  if (confirm(`Delete reconciliation entry from ${record.date}? This cannot be undone.`)) {
    records.value = records.value.filter(r => r !== record)
  }
}
</script>

<template>
  <FinanceListWorkspace title="Reconciliation" subtitle="Match bank feed activity to ledger transactions" action-label="Reconcile Selected" action-icon="ri-check-line" :metrics="metrics" :columns="columns" :records="records" :filters="['Matched','Review','Pending']" :insight="{title:'Reconciliation is backend-driven',text:'Suggested matches and pending items are derived from bank transactions and journal links.'}" :current-page="currentPage" :total-pages="totalPages" :total-records="totalRecords" :loading="loading" @primary-action="handlePrimaryAction" @edit-action="handleEdit" @delete-action="handleDelete" @next-page="nextPage" @prev-page="prevPage" @go-to-page="goToPage" />
</template>
