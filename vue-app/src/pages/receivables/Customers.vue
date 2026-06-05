<script setup>
import { ref } from 'vue'
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import { receivablesService } from '@/services/receivablesService'

const { metrics, records, currentPage, totalPages, totalRecords, loading, nextPage, prevPage, goToPage } = useApiPayload(receivablesService.customers)
const columns = [
  { key: 'name', label: 'Customer', primary: true },
  { key: 'contact', label: 'Primary Contact' },
  { key: 'terms', label: 'Terms' },
  { key: 'credit', label: 'Credit Limit', type: 'money' },
  { key: 'balance', label: 'Open Balance', type: 'money' },
  { key: 'status', label: 'Status', type: 'status' },
]

const showModal = ref(false)
const editingRecord = ref(null)
const modalForm = ref({ name: '', contact: '', email: '', terms: 'Net 30', credit_limit: 0, status: 'Active' })

function openModal(record = null) {
  editingRecord.value = record
  if (record) {
    modalForm.value = { name: record.name, contact: record.contact, email: record.email || '', terms: record.terms, credit_limit: record.credit, status: record.status }
  } else {
    modalForm.value = { name: '', contact: '', email: '', terms: 'Net 30', credit_limit: 0, status: 'Active' }
  }
  showModal.value = true
}

const saveCustomer = async () => {
  try {
    if (editingRecord.value) {
      await receivablesService.updateCustomer(editingRecord.value.id, modalForm.value)
    } else {
      await receivablesService.createCustomer(modalForm.value)
    }
    showModal.value = false
    modalForm.value = { name: '', contact: '', email: '', terms: 'Net 30', credit_limit: 0, status: 'Active' }
    const data = await receivablesService.customers()
    records.value = data.records || []
    metrics.value = data.metrics || []
  } catch (error) {
    alert('Error saving customer.')
  }
}

const handleEdit = (record) => { openModal(record) }

const handleDelete = async (record) => {
  if (!confirm(`Delete customer "${record.name}"? This cannot be undone.`)) return
  try {
    await receivablesService.deleteCustomer(record.id)
  } catch (e) {
    console.warn('Backend not available — customer removed locally')
  }
  records.value = records.value.filter(r => r !== record)
}
</script>

<template>
  <div>
    <FinanceListWorkspace title="Customers" subtitle="Manage customer credit, balances, and collection health" action-label="Add Customer" :metrics="metrics" :columns="columns" :records="records" :filters="['Active','Review']" detail-base="/receivables/customers" :insight="{title:'Customer balances are live',text:'Open balances are calculated from invoices and payments on the backend.'}" :current-page="currentPage" :total-pages="totalPages" :total-records="totalRecords" :loading="loading" @primary-action="openModal()" @edit-action="handleEdit" @delete-action="handleDelete" @next-page="nextPage" @prev-page="prevPage" @go-to-page="goToPage" />

    <!-- Add Customer Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-800">{{ editingRecord ? 'Edit Customer' : 'Add Customer' }}</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
            <input v-model="modalForm.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Customer name">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Primary Contact</label>
              <input v-model="modalForm.contact" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contact person">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input v-model="modalForm.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="email@example.com">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Terms</label>
              <select v-model="modalForm.terms" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option>Net 15</option>
                <option>Net 30</option>
                <option>Net 45</option>
                <option>Net 60</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Credit Limit (GHC)</label>
              <input v-model.number="modalForm.credit_limit" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="modalForm.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
              <option value="Active">Active</option>
              <option value="Review">Review</option>
            </select>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
          <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
          <button @click="saveCustomer" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 transition-colors shadow-sm">{{ editingRecord ? 'Update Customer' : 'Save Customer' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
