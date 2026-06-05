<script setup>
import { ref } from 'vue'
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import { receivablesService } from '@/services/receivablesService'

const { metrics, records, currentPage, totalPages, totalRecords, loading, nextPage, prevPage, goToPage } = useApiPayload(receivablesService.receipts)
const columns = [
  { key: 'reference', label: 'Receipt Ref', primary: true },
  { key: 'customer', label: 'Customer' },
  { key: 'date', label: 'Date' },
  { key: 'method', label: 'Method' },
  { key: 'amount', label: 'Amount', type: 'money' },
  { key: 'status', label: 'Status', type: 'status' },
]

const showModal = ref(false)
const modalForm = ref({ customer: '', reference: '', date: '', method: 'Bank Transfer', amount: 0, status: 'Review' })

const saveReceipt = async () => {
  try {
    await receivablesService.createReceipt(modalForm.value)
    showModal.value = false
    modalForm.value = { customer: '', reference: '', date: '', method: 'Bank Transfer', amount: 0, status: 'Review' }
    const data = await receivablesService.receipts()
    records.value = data.records || []
    metrics.value = data.metrics || []
  } catch (error) {
    alert('Error saving receipt.')
  }
}
</script>

<template>
  <div>
    <FinanceListWorkspace title="Receipts" subtitle="Apply and reconcile customer payments" action-label="Record Receipt" action-icon="ri-add-line" :metrics="metrics" :columns="columns" :records="records" :filters="['Matched','Review','Partial']" :insight="{title:'Receipts are loaded from payments',text:'Customer receipt rows are derived from backend payment records and invoice relationships.'}" :current-page="currentPage" :total-pages="totalPages" :total-records="totalRecords" :loading="loading" @primary-action="showModal = true" @next-page="nextPage" @prev-page="prevPage" @go-to-page="goToPage" />

    <!-- Record Receipt Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-800">Record Receipt</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
            <input v-model="modalForm.customer" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Customer name">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
              <input v-model="modalForm.reference" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="RCT-001">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
              <input v-model="modalForm.date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
              <select v-model="modalForm.method" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option>Bank Transfer</option>
                <option>Cash</option>
                <option>Cheque</option>
                <option>Mobile Money</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Amount (GHC)</label>
              <input v-model.number="modalForm.amount" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="modalForm.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
              <option value="Review">Review</option>
              <option value="Matched">Matched</option>
              <option value="Partial">Partial</option>
            </select>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
          <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
          <button @click="saveReceipt" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 transition-colors shadow-sm">Save Receipt</button>
        </div>
      </div>
    </div>
  </div>
</template>
