<script setup>
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import { reportsService } from '@/services/reportsService'

const { metrics, records } = useApiPayload(reportsService.auditTrail)
const columns = [
  { key: 'time', label: 'Time', primary: true },
  { key: 'user', label: 'User' },
  { key: 'action', label: 'Action' },
  { key: 'record', label: 'Record' },
  { key: 'ip', label: 'IP / Source' },
  { key: 'status', label: 'Status', type: 'status' },
]

const handleEdit = (record) => {
  alert(`Cannot edit audit log entry at ${record.time}`)
}

const handleDelete = (record) => {
  if (confirm(`Delete audit log entry from ${record.time}? This cannot be undone.`)) {
    records.value = records.value.filter(r => r !== record)
  }
}
</script>

<template>
  <FinanceListWorkspace title="Audit Trail" subtitle="Trace every financial event and control change" action-label="Export Audit Log" action-icon="ri-download-2-line" :metrics="metrics" :columns="columns" :records="records" :filters="['Completed','Review']" :insight="{title:'Audit trail is backend-owned',text:'Events are read from audit logs and enriched with user information.'}" @edit-action="handleEdit" @delete-action="handleDelete" />
</template>
