<script setup>
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import api from '@/services/api'

const { metrics, records, currentPage, totalPages, totalRecords, loading, nextPage, prevPage, goToPage } = useApiPayload(() => api.get('/settings/roles'))
const columns = [
  { key: 'name', label: 'Role Name', primary: true },
  { key: 'description', label: 'Description' },
  { key: 'users', label: 'Assigned Users' },
  { key: 'modules', label: 'Module Access' },
  { key: 'updated', label: 'Last Updated' },
  { key: 'status', label: 'Status', type: 'status' },
]

const handlePrimaryAction = () => {
  console.log('Create Role — create flow not yet implemented')
}

const handleEdit = (record) => {
  alert(`Edit role "${record.name}"`)
}

const handleDelete = (record) => {
  if (confirm(`Delete role "${record.name}"? This cannot be undone.`)) {
    records.value = records.value.filter(r => r !== record)
  }
}
</script>

<template>
  <FinanceListWorkspace title="Roles" subtitle="Configure role-based access and segregation of duties" action-label="Create Role" action-icon="ri-add-line" :metrics="metrics" :columns="columns" :records="records" :filters="['Active']" :insight="{title:'Roles are backend-owned',text:'Role rows and counts are read from backend access records.'}" :current-page="currentPage" :total-pages="totalPages" :total-records="totalRecords" :loading="loading" @primary-action="handlePrimaryAction" @edit-action="handleEdit" @delete-action="handleDelete" @next-page="nextPage" @prev-page="prevPage" @go-to-page="goToPage" />
</template>
