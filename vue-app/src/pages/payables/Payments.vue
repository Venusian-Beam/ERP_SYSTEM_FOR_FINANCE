<script setup>
import { ref } from 'vue'
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import { payablesService } from '@/services/payablesService'

const { metrics, records, currentPage, totalPages, totalRecords, loading, nextPage, prevPage, goToPage } = useApiPayload(payablesService.payments)
const columns = [
  { key: 'reference', label: 'Payment Ref', primary: true },
  { key: 'vendor', label: 'Vendor' },
  { key: 'method', label: 'Method' },
  { key: 'bank', label: 'Bank Account' },
  { key: 'amount', label: 'Amount', type: 'money' },
  { key: 'status', label: 'Status', type: 'status' },
]

const showModal = ref(false)
const modalForm = ref({ vendor: '', amount: 0, method: 'Bank Transfer', bank: '', date: '', reference: '', status: 'Pending' })

const savePayment = async () => {
  try {
    await payablesService.createPayment(modalForm.value)
    showModal.value = false
    modalForm.value = { vendor: '', amount: 0, method: 'Bank Transfer', bank: '', date: '', reference: '', status: 'Pending' }
    const data = await payablesService.payments()
    records.value = data.records || []
    metrics.value = data.metrics || []
  } catch (error) {
    alert('Error saving payment.')
  }
}
</script>

<template>
  <div>
    <FinanceListWorkspace title="Payments" subtitle="Plan and monitor supplier disbursements" action-label="Create Payment Run" action-icon="ri-play-list-add-line" :metrics="metrics" :columns="columns" :records="records" :filters="['Scheduled','Pending','Paid']" :insight="{title:'Payments are backend-driven',text:'Payment rows come from the payments table with related invoice details.'}" :current-page="currentPage" :total-pages="totalPages" :total-records="totalRecords" :loading="loading" @primary-action="showModal = true" @next-page="nextPage" @prev-page="prevPage" @go-to-page="goToPage" />

    <!-- Create Payment Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-800">Create Payment Run</h3>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (GHC)</label>
            <input v-model.number="modalForm.amount" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
              <select v-model="modalForm.method" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option>Bank Transfer</option>
                <option>Cheque</option>
                <option>Cash</option>
                <option>Mobile Money</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account</label>
              <input v-model="modalForm.bank" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. GCB Business">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
            <input v-model="modalForm.date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
            <input v-model="modalForm.reference" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. PMT-001">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="modalForm.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
              <option value="Pending">Pending</option>
              <option value="Scheduled">Scheduled</option>
              <option value="Paid">Paid</option>
            </select>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
          <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
          <button @click="savePayment" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 transition-colors shadow-sm">Save Payment</button>
        </div>
      </div>
    </div>
  </div>
</template>
