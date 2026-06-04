<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PageHeader from '@/components/ui/PageHeader.vue'
import apiClient from '@/utils/apiClient'
import { exportToCSV } from '@/utils/exportUtils'

const router = useRouter()
const searchQuery = ref('')
const statusFilter = ref('all')
const projects = ref([])
const loading = ref(false)

// Modal state
const showModal = ref(false)
const editingProject = ref(null)
const modalForm = ref({ name: '', status: 'planning', due_date: '', budget_amount: '' })

const fetchProjects = async () => {
  loading.value = true
  try {
    const { data } = await apiClient.get('/projects')
    projects.value = data
  } catch (e) {
    console.error('Failed to load projects:', e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchProjects)

const filteredProjects = computed(() => {
  return projects.value.filter(p => {
    const matchesSearch = p.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesStatus = statusFilter.value === 'all' || p.status === statusFilter.value
    return matchesSearch && matchesStatus
  })
})

const openCreateModal = () => {
  editingProject.value = null
  modalForm.value = { name: '', status: 'planning', due_date: '', budget_amount: '' }
  showModal.value = true
}

const openEditModal = (project) => {
  editingProject.value = project
  modalForm.value = {
    name: project.name,
    status: project.status,
    due_date: project.due_date,
    budget_amount: project.budget_amount
  }
  showModal.value = true
}

const saveProject = async () => {
  try {
    if (editingProject.value) {
      const { data } = await apiClient.put(`/projects/${editingProject.value.id}`, modalForm.value)
      const idx = projects.value.findIndex(p => p.id === data.id)
      if (idx !== -1) projects.value[idx] = data
    } else {
      const { data } = await apiClient.post('/projects', modalForm.value)
      projects.value.unshift(data)
    }
    showModal.value = false
  } catch (e) {
    alert('Error saving project.')
  }
}

const deleteProject = async (project) => {
  if (!confirm(`Permanently delete "${project.name}" and all its tasks?`)) return
  try {
    await apiClient.delete(`/projects/${project.id}`)
    projects.value = projects.value.filter(p => p.id !== project.id)
  } catch (e) {
    alert('Error deleting project.')
  }
}

const getStatusClass = (status) => ({
  'planning': 'bg-info/10 text-info',
  'in-progress': 'bg-primary/10 text-primary',
  'on-hold': 'bg-warning/10 text-warning',
  'completed': 'bg-success/10 text-success'
})[status] || 'bg-secondary/10 text-secondary'

const getPriorityClass = (priority) => ({
  'high': 'bg-danger/10 text-danger',
  'medium': 'bg-warning/10 text-warning',
  'low': 'bg-success/10 text-success'
})[priority] || 'bg-secondary/10 text-secondary'

const formatDate = (dateStr) => {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatCurrency = (amount) => {
  if (!amount) return '$0'
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(amount)
}

const handleExport = () => {
  exportToCSV(filteredProjects.value, [
    { label: 'Project Name', key: 'name' },
    { label: 'Status', key: 'status' },
    { label: 'Due Date', key: 'due_date' },
    { label: 'Budget', key: 'budget_amount', type: 'money' },
    { label: 'Progress', key: 'progress' }
  ], 'projects_export.csv')
}
</script>

<template>
  <div>
    <PageHeader title="Projects List" subtitle="Manage and track all your projects">
      <template #actions>
        <button class="ti-btn ti-btn-light ti-btn-sm" @click="handleExport">
          <i class="ri-download-line me-1"></i> Export
        </button>
        <button class="ti-btn ti-btn-primary btn-wave" @click="openCreateModal">
          <i class="ri-add-line me-1"></i> New Project
        </button>
      </template>
    </PageHeader>

    <div class="box">
      <div class="box-header flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="relative">
            <input v-model="searchQuery" type="text" class="ti-form-control !ps-10" placeholder="Search projects...">
            <i class="ri-search-line absolute start-3 top-1/2 -translate-y-1/2 text-textmuted"></i>
          </div>
          <select v-model="statusFilter" class="ti-form-select w-auto">
            <option value="all">All Status</option>
            <option value="planning">Planning</option>
            <option value="in-progress">In Progress</option>
            <option value="on-hold">On Hold</option>
            <option value="completed">Completed</option>
          </select>
        </div>
      </div>

      <div class="box-body p-0">
        <div class="table-responsive">
          <table class="table table-hover whitespace-nowrap">
            <thead>
              <tr>
                <th>Project</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Budget</th>
                <th>Due Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="text-center py-6 text-textmuted">
                  <i class="ri-loader-4-line animate-spin me-2"></i> Loading projects...
                </td>
              </tr>
              <tr v-else v-for="project in filteredProjects" :key="project.id">
                <td>
                  <div class="flex items-center gap-3">
                    <span class="avatar avatar-md bg-primary/10 text-primary avatar-rounded flex items-center justify-center">
                      <i class="ri-folder-line text-lg"></i>
                    </span>
                    <div>
                      <router-link :to="`/projects/${project.id}`" class="font-medium text-defaulttextcolor hover:text-primary">
                        {{ project.name }}
                      </router-link>
                      <p class="text-textmuted text-xs mb-0">{{ project.code || 'No code' }}</p>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge" :class="getStatusClass(project.status)">
                    {{ project.status ? project.status.replace('-', ' ') : 'Planning' }}
                  </span>
                </td>
                <td>
                  <div class="flex items-center gap-2 min-w-[120px]">
                    <div class="progress progress-xs flex-1">
                      <div class="progress-bar bg-primary" :style="{ width: (project.progress || 0) + '%' }"></div>
                    </div>
                    <span class="text-xs text-textmuted">{{ project.progress || 0 }}%</span>
                  </div>
                </td>
                <td>{{ formatCurrency(project.budget_amount) }}</td>
                <td>{{ formatDate(project.due_date) }}</td>
                <td>
                  <div class="flex gap-1">
                    <router-link :to="`/projects/${project.id}`" class="ti-btn ti-btn-soft-primary ti-btn-icon ti-btn-sm">
                      <i class="ri-eye-line"></i>
                    </router-link>
                    <button class="ti-btn ti-btn-soft-info ti-btn-icon ti-btn-sm" @click="openEditModal(project)">
                      <i class="ri-edit-line"></i>
                    </button>
                    <button class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm" @click="deleteProject(project)">
                      <i class="ri-delete-bin-line"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!loading && filteredProjects.length === 0">
                <td colspan="6" class="text-center py-6 text-textmuted">No projects found. Click "New Project" to create one.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="box-footer flex items-center justify-between">
        <div class="text-textmuted text-sm">
          Showing {{ filteredProjects.length }} of {{ projects.length }} projects
        </div>
      </div>
    </div>

    <!-- Create / Edit Project Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-800">{{ editingProject ? 'Edit Project' : 'New Project' }}</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Project Name <span class="text-danger">*</span></label>
            <input v-model="modalForm.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors" placeholder="e.g. Website Redesign">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="modalForm.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
              <option value="planning">Planning</option>
              <option value="in-progress">In Progress</option>
              <option value="on-hold">On Hold</option>
              <option value="completed">Completed</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
            <input v-model="modalForm.due_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Budget (USD)</label>
            <input v-model.number="modalForm.budget_amount" type="number" step="100" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors" placeholder="e.g. 50000">
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
          <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
          <button @click="saveProject" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 transition-colors shadow-sm">
            {{ editingProject ? 'Save Changes' : 'Create Project' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
