<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PageHeader from '@/components/ui/PageHeader.vue'
import { payablesService } from '@/services/payablesService'
import { exportToCSV } from '@/utils/exportUtils'

const router = useRouter()
const searchQuery = ref('')
const selectedCategory = ref('all')
const vendors = ref([])
const loading = ref(false)

// Modal State
const showModal = ref(false)
const editingVendor = ref(null)
const modalForm = ref({ name: '', email: '', phone: '', status: 'active' })

const fetchVendors = async () => {
  loading.value = true
  try {
    const data = await payablesService.vendors()
    vendors.value = data.records || data.data || data
  } catch (e) {
    console.error("Failed to load vendors")
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchVendors()
})

const filteredVendors = computed(() => {
  return vendors.value.filter(v => {
    const matchesSearch = v.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                          (v.email && v.email.toLowerCase().includes(searchQuery.value.toLowerCase()))
    return matchesSearch
  })
})

const viewVendor = (id) => router.push(`/payables/vendors/${id}`)

const openAddModal = () => {
  editingVendor.value = null
  modalForm.value = { name: '', email: '', phone: '', status: 'active' }
  showModal.value = true
}

const editVendor = (vendor) => {
  editingVendor.value = vendor
  modalForm.value = { name: vendor.name, email: vendor.email, phone: vendor.phone, status: vendor.status }
  showModal.value = true
}

const handleExport = () => {
  exportToCSV(filteredVendors.value, [
    { label: 'Vendor Name', key: 'name' },
    { label: 'Contact', key: 'email' },
    { label: 'Status', key: 'status' }
  ], 'vendors_export.csv')
}

const saveVendor = async () => {
  try {
    if (editingVendor.value) {
      await payablesService.updateVendor(editingVendor.value.id, modalForm.value)
    } else {
      await payablesService.createVendor(modalForm.value)
    }
    showModal.value = false
    editingVendor.value = null
    modalForm.value = { name: '', email: '', phone: '', status: 'active' }
    fetchVendors()
  } catch (error) {
    alert("Error saving vendor. Check console.")
  }
}

const deleteVendor = async (id) => {
  if(!confirm("Are you sure you want to delete this vendor?")) return;
  try {
    await payablesService.deleteVendor(id)
    vendors.value = vendors.value.filter(v => v.id !== id)
  } catch (e) {
    alert("Error deleting vendor")
  }
}
</script>

<template>
  <div>
    <PageHeader title="Vendors" subtitle="Manage supplier records and payment terms">
      <template #actions>
        <button class="ti-btn btn-primary-soft" @click="handleExport">
          <i class="ri-download-2-line"></i> Export
        </button>
        <button class="ti-btn btn-gradient" @click="openAddModal">
          <i class="ri-add-line"></i> Add Vendor
        </button>
      </template>
    </PageHeader>

    <div class="box">
      <div class="box-body">
        <div class="table-toolbar">
          <div class="search-box">
            <i class="ri-search-line search-icon"></i>
            <input type="text" v-model="searchQuery" class="form-control" placeholder="Search vendors...">
          </div>
        </div>

        <div class="table-responsive">
          <table class="table-standard">
            <thead>
              <tr>
                <th>Vendor Name</th>
                <th>Contact Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="5" class="text-center py-4 text-muted"><i class="ri-loader-4-line spin"></i> Loading...</td>
              </tr>
              <tr v-else v-for="vendor in filteredVendors" :key="vendor.id">
                <td class="font-medium">{{ vendor.name }}</td>
                <td class="text-muted">{{ vendor.email || 'N/A' }}</td>
                <td class="text-muted">{{ vendor.phone || 'N/A' }}</td>
                <td>
                  <span class="badge" :class="vendor.status === 'active' ? 'bg-success-transparent' : 'bg-light text-dark'">
                    {{ vendor.status }}
                  </span>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-icon btn-primary-light" @click="viewVendor(vendor.id)">
                    <i class="ri-eye-line"></i>
                  </button>
                  <button class="ti-btn ti-btn-soft-info ti-btn-icon ti-btn-sm" @click="editVendor(vendor)">
                    <i class="ri-edit-line"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-danger-light ms-1" @click="deleteVendor(vendor.id)">
                    <i class="ri-delete-bin-line"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!loading && filteredVendors.length === 0">
                <td colspan="5" class="text-center py-4 text-muted">No vendors found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add Vendor Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-800">{{ editingVendor ? 'Edit Vendor' : 'Add New Vendor' }}</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
            <input v-model="modalForm.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary transition-colors" placeholder="Acme Corp">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input v-model="modalForm.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary transition-colors" placeholder="billing@example.com">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
            <input v-model="modalForm.phone" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary transition-colors" placeholder="+1 (555) 000-0000">
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
          <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
          <button @click="saveVendor" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 transition-colors shadow-sm">{{ editingVendor ? 'Update Vendor' : 'Save Vendor' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.table-toolbar {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
  gap: 1rem;
}

.search-box {
  position: relative;
  max-width: 300px;
  width: 100%;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}

.search-box .form-control {
  padding-left: 2.5rem;
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  height: 2.5rem;
  width: 100%;
}

.form-select {
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  height: 2.5rem;
  padding: 0 1rem;
  outline: none;
}
.form-select:focus {
  border-color: var(--primary);
}

.btn-icon {
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-sm);
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary-light { background: rgba(99,102,241,0.1); color: var(--primary); }
.btn-primary-light:hover { background: var(--primary); color: white; }

.btn-info-light { background: rgba(14,165,233,0.1); color: #0ea5e9; }
.btn-info-light:hover { background: #0ea5e9; color: white; }

.btn-gradient {
  background: linear-gradient(135deg, var(--primary), var(--primarytint1color));
  color: white;
  border: none;
  box-shadow: 0 4px 10px rgba(92, 103, 247, 0.25);
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: var(--radius-md);
  cursor: pointer;
}
.btn-gradient:hover { filter: brightness(1.1); }
.btn-primary-soft {
  background: rgba(92, 103, 247, 0.1);
  color: var(--primary);
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: var(--radius-md);
  cursor: pointer;
}
.btn-primary-soft:hover { background: var(--primary); color: white; }
.ms-1 { margin-left: 0.25rem; }
.badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}
.bg-primary-transparent { background: rgba(99,102,241,0.1); color: var(--primary); }
.bg-success-transparent { background: rgba(34,197,94,0.1); color: #22c55e; }
.bg-light { background: #f1f5f9; color: #475569; }
.text-danger { color: #ef4444; }
.text-success { color: #22c55e; }
</style>
