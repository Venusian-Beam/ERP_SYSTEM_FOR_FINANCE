<script setup>
import { ref, computed, onMounted } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import apiClient from '@/utils/apiClient'

const searchQuery = ref('')
const statusFilter = ref('all')
const tasks = ref([])
const loading = ref(false)

// Modal state
const showModal = ref(false)
const editingTask = ref(null)
const modalForm = ref({ title: '', status: 'pending', priority: 'medium', due_date: '' })

const fetchTasks = async () => {
  loading.value = true
  try {
    const { data } = await apiClient.get('/project-tasks')
    tasks.value = data
  } catch (e) {
    console.error('Failed to load tasks:', e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchTasks)

const filteredTasks = computed(() => {
  return tasks.value.filter(task => {
    const matchesSearch = task.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesStatus = statusFilter.value === 'all' || task.status === statusFilter.value
    return matchesSearch && matchesStatus
  })
})

const openCreateModal = () => {
  editingTask.value = null
  modalForm.value = { title: '', status: 'pending', priority: 'medium', due_date: '' }
  showModal.value = true
}

const openEditModal = (task) => {
  editingTask.value = task
  modalForm.value = { title: task.title, status: task.status, priority: task.priority, due_date: task.due_date }
  showModal.value = true
}

const saveTask = async () => {
  if (!modalForm.value.title.trim()) { alert('Task title is required.'); return }
  try {
    if (editingTask.value) {
      const { data } = await apiClient.put(`/project-tasks/${editingTask.value.id}`, modalForm.value)
      const idx = tasks.value.findIndex(t => t.id === data.id)
      if (idx !== -1) tasks.value[idx] = data
    } else {
      const { data } = await apiClient.post('/project-tasks', modalForm.value)
      tasks.value.unshift(data)
    }
    showModal.value = false
  } catch (e) {
    alert('Error saving task.')
  }
}

const deleteTask = async (task) => {
  if (!confirm(`Permanently delete task "${task.title}"?`)) return
  try {
    await apiClient.delete(`/project-tasks/${task.id}`)
    tasks.value = tasks.value.filter(t => t.id !== task.id)
  } catch (e) {
    alert('Error deleting task.')
  }
}

const getStatusClass = (status) => ({
  'completed': 'bg-success/10 text-success',
  'in-progress': 'bg-primary/10 text-primary',
  'pending': 'bg-warning/10 text-warning'
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
</script>

<template>
  <div>
    <PageHeader title="Task List" subtitle="Manage all tasks across projects">
      <template #actions>
        <button class="ti-btn ti-btn-primary" @click="openCreateModal">
          <i class="ri-add-line me-1"></i> New Task
        </button>
      </template>
    </PageHeader>

    <div class="box">
      <div class="box-header flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="relative">
            <input v-model="searchQuery" type="text" class="ti-form-control !ps-10" placeholder="Search tasks...">
            <i class="ri-search-line absolute start-3 top-1/2 -translate-y-1/2 text-textmuted"></i>
          </div>
          <select v-model="statusFilter" class="ti-form-select w-auto">
            <option value="all">All Status</option>
            <option value="pending">Pending</option>
            <option value="in-progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
        </div>
      </div>

      <div class="box-body p-0">
        <div class="table-responsive">
          <table class="table table-hover whitespace-nowrap">
            <thead>
              <tr>
                <th>Task</th>
                <th>Project</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Due Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="text-center py-6 text-textmuted">
                  <i class="ri-loader-4-line animate-spin me-2"></i> Loading tasks...
                </td>
              </tr>
              <tr v-else v-for="task in filteredTasks" :key="task.id">
                <td class="font-medium">{{ task.title }}</td>
                <td class="text-textmuted">{{ task.project ? task.project.name : '—' }}</td>
                <td><span class="badge" :class="getStatusClass(task.status)">{{ task.status }}</span></td>
                <td><span class="badge" :class="getPriorityClass(task.priority)">{{ task.priority }}</span></td>
                <td>{{ formatDate(task.due_date) }}</td>
                <td>
                  <div class="flex gap-1">
                    <button class="ti-btn ti-btn-soft-info ti-btn-icon ti-btn-sm" @click="openEditModal(task)">
                      <i class="ri-edit-line"></i>
                    </button>
                    <button class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm" @click="deleteTask(task)">
                      <i class="ri-delete-bin-line"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!loading && filteredTasks.length === 0">
                <td colspan="6" class="text-center py-6 text-textmuted">No tasks found. Click "New Task" to add one.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create / Edit Task Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-800">{{ editingTask ? 'Edit Task' : 'New Task' }}</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Task Title <span class="text-danger">*</span></label>
            <input v-model="modalForm.title" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors" placeholder="e.g. Design homepage mockup">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select v-model="modalForm.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                <option value="pending">Pending</option>
                <option value="in-progress">In Progress</option>
                <option value="completed">Completed</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
              <select v-model="modalForm.priority" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
            <input v-model="modalForm.due_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
          <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
          <button @click="saveTask" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 transition-colors shadow-sm">
            {{ editingTask ? 'Save Changes' : 'Create Task' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
