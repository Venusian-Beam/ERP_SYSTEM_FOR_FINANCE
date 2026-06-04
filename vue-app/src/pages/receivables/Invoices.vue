<script setup>
import { ref, onMounted } from 'vue'
import FinanceListWorkspace from '@/components/finance/FinanceListWorkspace.vue'
import apiClient from '@/utils/apiClient'

const metrics = [
  {label:'Outstanding invoices',value:'GHC 342,780',trend:'62 open invoices',icon:'ri-bill-line'},
  {label:'Overdue',value:'GHC 48,250',trend:'9 invoices past due',icon:'ri-error-warning-line',tone:'danger'},
  {label:'Collected this month',value:'GHC 418,600',trend:'+18.3% vs last month',icon:'ri-hand-coin-line',tone:'success'},
  {label:'Draft invoices',value:'GHC 27,900',trend:'6 ready to send',icon:'ri-draft-line',tone:'warning'}
]

const columns = [
  {key:'invoice_number',label:'Invoice No.',primary:true},
  {key:'customer_name',label:'Customer'},
  {key:'invoice_date',label:'Issue Date'},
  {key:'due_date',label:'Due Date'},
  {key:'amount',label:'Amount',type:'money'},
  {key:'status',label:'Status',type:'status'}
]

const records = ref([])
const showModal = ref(false)
const modalForm = ref({ customer_id: 1, invoice_number: '', invoice_date: '', due_date: '', amount: 0, status: 'Open' })

const fetchInvoices = async () => {
  try {
    const { data } = await apiClient.get('/invoices')
    records.value = data.map(inv => ({
      ...inv,
      customer_name: inv.customer ? inv.customer.name : 'Unknown'
    }))
  } catch (e) {
    console.error("Failed to load invoices")
  }
}

onMounted(() => {
  fetchInvoices()
})

const saveInvoice = async () => {
  try {
    await apiClient.post('/invoices', modalForm.value)
    showModal.value = false
    modalForm.value = { customer_id: 1, invoice_number: '', invoice_date: '', due_date: '', amount: 0, status: 'Open' }
    fetchInvoices()
  } catch (error) {
    alert("Error saving invoice.")
  }
}
</script>

<template>
  <div>
    <FinanceListWorkspace 
      title="Invoices" 
      subtitle="Create, send, and collect customer invoices" 
      action-label="New Invoice" 
      :metrics="metrics" 
      :columns="columns" 
      :records="records" 
      :filters="['Open','Overdue','Paid']" 
      detail-base="/receivables/invoices" 
      @primary-action="showModal = true"
      :insight="{title:'Collection opportunity',text:'Automated reminders could accelerate GHC 82,450 currently due within the next seven days.'}" 
    />

    <!-- Add Invoice Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-800">Create New Invoice</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Number</label>
            <input v-model="modalForm.invoice_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors" placeholder="INV-2085">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Issue Date</label>
            <input v-model="modalForm.invoice_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
            <input v-model="modalForm.due_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (GHC)</label>
            <input v-model.number="modalForm.amount" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="modalForm.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
              <option value="Open">Open</option>
              <option value="Paid">Paid</option>
              <option value="Overdue">Overdue</option>
            </select>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
          <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
          <button @click="saveInvoice" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 transition-colors shadow-sm">Save Invoice</button>
        </div>
      </div>
    </div>
  </div>
</template>
