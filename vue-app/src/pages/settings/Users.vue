<script setup>
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import api from '@/services/api'

const { metrics, records, currentPage, totalPages, totalRecords, loading, nextPage, prevPage, goToPage } = useApiPayload(() => api.get('/settings/users'))
const columns = [
  { key: 'name', label: 'User', primary: true },
  { key: 'email', label: 'Email Address' },
  { key: 'role', label: 'Role' },
  { key: 'modules', label: 'Module Access' },
  { key: 'lastActive', label: 'Last Active' },
  { key: 'status', label: 'Status', type: 'status' },
]

const handlePrimaryAction = () => {
  console.log('Invite User — create flow not yet implemented')
}
</script>

<template>
  <FinanceListWorkspace title="Users" subtitle="Invite team members and manage finance system access" action-label="Invite User" action-icon="ri-user-add-line" :metrics="metrics" :columns="columns" :records="records" :filters="['Active','Pending']" :insight="{title:'Users are loaded from the backend',text:'User access rows are fetched from backend user records for the active tenant.'}" :current-page="currentPage" :total-pages="totalPages" :total-records="totalRecords" :loading="loading" @primary-action="handlePrimaryAction" @next-page="nextPage" @prev-page="prevPage" @go-to-page="goToPage" />
</template>
