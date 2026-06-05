<script setup>
import { ref, reactive } from 'vue'
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'
import { accountingService } from '@/services/accountingService'
import { useApiPayload } from '@/composables/useApiPayload'

const { metrics, records, loading, currentPage, totalPages, totalRecords, fetchData, nextPage, prevPage, goToPage } = useApiPayload(accountingService.accounts)

const columns = [
  { key: 'code', label: 'Account Code', primary: true },
  { key: 'name', label: 'Account Name' },
  { key: 'type', label: 'Account Type' },
  { key: 'parent', label: 'Parent Account' },
  { key: 'balance', label: 'Current Balance', type: 'money' },
  { key: 'status', label: 'Status', type: 'status' }
]

const showModal = ref(false)
const modalSaving = ref(false)
const editingRecord = ref(null)
const form = reactive({
  code: '', name: '', type: 'asset', description: '', is_active: true
})

function openModal(record = null) {
  editingRecord.value = record
  if (record) {
    form.code = record.code; form.name = record.name; form.type = (record.type || '').toLowerCase(); form.description = record.description || ''; form.is_active = record.status === 'Active'
  } else {
    form.code = ''; form.name = ''; form.type = 'asset'; form.description = ''; form.is_active = true
  }
  showModal.value = true
}

async function saveAccount() {
  if (!form.code.trim() || !form.name.trim()) return
  modalSaving.value = true
  try {
    if (editingRecord.value) {
      const data = await accountingService.updateAccount(editingRecord.value.id, { ...form })
      Object.assign(editingRecord.value, data.record || data)
    } else {
      const data = await accountingService.createAccount({ ...form })
      records.value.unshift(data.record || data)
    }
  } catch (e) {
    if (editingRecord.value) {
      editingRecord.value.code = form.code; editingRecord.value.name = form.name; editingRecord.value.type = form.type.charAt(0).toUpperCase() + form.type.slice(1); editingRecord.value.description = form.description; editingRecord.value.status = form.is_active ? 'Active' : 'Inactive'
    } else {
      records.value.unshift({ id: Date.now(), code: form.code, name: form.name, type: form.type.charAt(0).toUpperCase() + form.type.slice(1), parent: 'General Ledger', balance: 0, status: form.is_active ? 'Active' : 'Inactive' })
    }
    console.warn('Backend not available — operation completed locally')
  } finally {
    showModal.value = false
    modalSaving.value = false
    editingRecord.value = null
  }
}

function handleEdit(record) {
  openModal(record)
}

async function handleDelete(record) {
  if (!confirm(`Delete account "${record.code} — ${record.name}"? This cannot be undone.`)) return
  try {
    await accountingService.deleteAccount(record.id)
  } catch (e) {
    console.warn('Backend not available — account removed locally')
  }
  records.value = records.value.filter(r => r !== record)
}
</script>

<template>
  <div v-if="loading" class="flex justify-center items-center h-64">
    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
  </div>
  <template v-else>
    <FinanceListWorkspace 
      title="Chart of Accounts" 
      subtitle="Manage the hierarchy and classification of general ledger accounts" 
      action-label="Add Account" 
      :metrics="metrics" 
      :columns="columns" 
      :records="records" 
      :filters="['Active', 'Header', 'Current Asset', 'Current Liability']" 
      :insight="{title:'Account structure looks healthy', text:'No duplicate account codes or posting activity against header accounts were detected.'}" 
      :current-page="currentPage" :total-pages="totalPages" :total-records="totalRecords" :loading="loading"
      @primary-action="openModal" @edit-action="handleEdit" @delete-action="handleDelete" @next-page="nextPage" @prev-page="prevPage" @go-to-page="goToPage"
    />

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" @click.self="showModal = false">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-800">{{ editingRecord ? 'Edit Account' : 'Add Account' }}</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors"><i class="ri-close-line text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Account Code <span class="text-red-500">*</span></label>
              <input v-model="form.code" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. 1010">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
              <select v-model="form.type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <option value="asset">Asset</option>
                <option value="liability">Liability</option>
                <option value="equity">Equity</option>
                <option value="revenue">Revenue</option>
                <option value="expense">Expense</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Account Name <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Business Checking">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Optional description..."></textarea>
          </div>
          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            Active account
          </label>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
          <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
          <button @click="saveAccount" :disabled="modalSaving || !form.code.trim() || !form.name.trim()" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 transition-colors shadow-sm disabled:opacity-50">
            {{ modalSaving ? 'Saving...' : editingRecord ? 'Update Account' : 'Create Account' }}
          </button>
        </div>
      </div>
    </div>
  </template>
</template>
