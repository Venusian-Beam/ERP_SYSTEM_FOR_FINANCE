<script setup>
import { ref } from 'vue'
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import { payablesService } from '@/services/payablesService'

const { metrics, records, currentPage, totalPages, totalRecords, loading, nextPage, prevPage, goToPage } = useApiPayload(payablesService.bills)
const columns = [
  { key: 'number', label: 'Bill No.', primary: true },
  { key: 'vendor', label: 'Vendor' },
  { key: 'date', label: 'Bill Date' },
  { key: 'due', label: 'Due Date' },
  { key: 'amount', label: 'Amount', type: 'money' },
  { key: 'status', label: 'Status', type: 'status' },
]

const showModal = ref(false)
const editingRecord = ref(null)
const modalForm = ref({ vendor: '', number: '', date: '', due: '', amount: 0, status: 'Open' })

function openModal(record = null) {
  editingRecord.value = record
  if (record) {
    modalForm.value = { vendor: record.vendor, number: record.number, date: record.date, due: record.due, amount: record.amount, status: record.status }
  } else {
    modalForm.value = { vendor: '', number: '', date: '', due: '', amount: 0, status: 'Open' }
  }
  showModal.value = true
}

const saveBill = async () => {
  try {
    if (editingRecord.value) {
      await payablesService.updateBill(editingRecord.value.id, modalForm.value)
    } else {
      await payablesService.createBill(modalForm.value)
    }
    showModal.value = false
    modalForm.value = { vendor: '', number: '', date: '', due: '', amount: 0, status: 'Open' }
    const data = await payablesService.bills()
    records.value = data.records || []
    metrics.value = data.metrics || []
  } catch (error) {
    alert('Error saving bill.')
  }
}

const handleEdit = (record) => { openModal(record) }

const handleDelete = async (record) => {
  if (!confirm(`Delete bill "${record.number}"? This cannot be undone.`)) return
  try {
    await payablesService.deleteBill(record.id)
  } catch (e) {
    console.warn('Backend not available — bill removed locally')
  }
  records.value = records.value.filter(r => r !== record)
}
</script>

<template>
  <div>
    <FinanceListWorkspace title="Bills" subtitle="Capture, approve, and manage vendor invoices" action-label="New Bill" action-icon="ri-add-line" :metrics="metrics" :columns="columns" :records="records" :filters="['Pending','Open','Overdue','Approved']" detail-base="/payables/bills" :insight="{title:'Bills are loaded from the backend',text:'Vendor invoice totals and due dates are calculated from supplier invoice records.'}" :current-page="currentPage" :total-pages="totalPages" :total-records="totalRecords" :loading="loading" @primary-action="openModal()" @edit-action="handleEdit" @delete-action="handleDelete" @next-page="nextPage" @prev-page="prevPage" @go-to-page="goToPage" />

    <!-- Add Bill Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-800">{{ editingRecord ? 'Edit Bill' : 'Create New Bill' }}</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
            <input v-model="modalForm.vendor" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Vendor name">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bill Number</label>
            <input v-model="modalForm.number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="BILL-1049">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bill Date</label>
              <input v-model="modalForm.date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
              <input v-model="modalForm.due" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (GHC)</label>
            <input v-model.number="modalForm.amount" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="modalForm.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
              <option value="Open">Open</option>
              <option value="Pending">Pending</option>
              <option value="Approved">Approved</option>
              <option value="Overdue">Overdue</option>
            </select>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
          <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
          <button @click="saveBill" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 transition-colors shadow-sm">{{ editingRecord ? 'Update Bill' : 'Save Bill' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
