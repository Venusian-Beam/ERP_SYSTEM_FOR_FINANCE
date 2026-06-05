<script setup>
import { ref } from 'vue'
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'
import { useApiPayload } from '@/composables/useApiPayload'
import { treasuryService } from '@/services/treasuryService'

const { metrics, records, currentPage, totalPages, totalRecords, loading, nextPage, prevPage, goToPage } = useApiPayload(treasuryService.bankAccounts)
const columns = [
  { key: 'name', label: 'Account', primary: true },
  { key: 'bank', label: 'Financial Institution' },
  { key: 'type', label: 'Type' },
  { key: 'synced', label: 'Last Synced' },
  { key: 'balance', label: 'Current Balance', type: 'money' },
  { key: 'status', label: 'Status', type: 'status' },
]

const showModal = ref(false)
const modalForm = ref({ name: '', bank: '', type: 'Checking', balance: 0, status: 'Active' })

const saveAccount = async () => {
  try {
    await treasuryService.createBankAccount(modalForm.value)
    showModal.value = false
    modalForm.value = { name: '', bank: '', type: 'Checking', balance: 0, status: 'Active' }
    const data = await treasuryService.bankAccounts()
    records.value = data.records || []
    metrics.value = data.metrics || []
  } catch (error) {
    alert('Error saving bank account.')
  }
}
</script>

<template>
  <div>
    <FinanceListWorkspace title="Bank Accounts" subtitle="Monitor connected bank balances and feed health" action-label="Connect Account" action-icon="ri-link-m" :metrics="metrics" :columns="columns" :records="records" :filters="['Active','Review']" :insight="{title:'Liquidity remains healthy',text:'Available cash is calculated from connected bank account balances in the backend.'}" :current-page="currentPage" :total-pages="totalPages" :total-records="totalRecords" :loading="loading" @primary-action="showModal = true" @next-page="nextPage" @prev-page="prevPage" @go-to-page="goToPage" />

    <!-- Connect Account Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-800">Connect Bank Account</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
            <input v-model="modalForm.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. GCB Business Account">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Financial Institution</label>
            <input v-model="modalForm.bank" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. GCB Bank">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
              <select v-model="modalForm.type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option>Checking</option>
                <option>Savings</option>
                <option>Money Market</option>
                <option>Credit Card</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Balance (GHC)</label>
              <input v-model.number="modalForm.balance" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
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
          <button @click="saveAccount" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 transition-colors shadow-sm">Connect Account</button>
        </div>
      </div>
    </div>
  </div>
</template>
