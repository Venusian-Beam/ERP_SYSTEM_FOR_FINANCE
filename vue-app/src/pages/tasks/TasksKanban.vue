<script setup>
import { ref, onMounted } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import apiClient from '@/utils/apiClient'

// Kanban columns (local UI state — tasks populated from API)
const columns = ref([
  { id: 'pending',     title: 'Backlog / Pending',   color: 'secondary', tasks: [] },
  { id: 'in-progress', title: 'In Progress',          color: 'primary',   tasks: [] },
  { id: 'completed',   title: 'Done / Cleared',       color: 'success',   tasks: [] },
])

const loading = ref(false)

// Global "Add Task" modal
const showModal = ref(false)
const activeColumnId = ref('pending')
const modalForm = ref({ title: '', priority: 'medium', due_date: '' })

const fetchTasks = async () => {
  loading.value = true
  try {
    const { data } = await apiClient.get('/project-tasks')
    // Sort tasks into their respective columns
    columns.value.forEach(col => { col.tasks = [] })
    data.forEach(task => {
      const col = columns.value.find(c => c.id === task.status)
      if (col) col.tasks.push(task)
      else columns.value[0].tasks.push(task) // default to backlog
    })
  } catch (e) {
    console.error('Failed to load tasks:', e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchTasks)

const openAddTask = (columnId) => {
  activeColumnId.value = columnId
  modalForm.value = { title: '', priority: 'medium', due_date: '' }
  showModal.value = true
}

const saveTask = async () => {
  if (!modalForm.value.title.trim()) { alert('Task title is required.'); return }
  try {
    const { data } = await apiClient.post('/project-tasks', {
      ...modalForm.value,
      status: activeColumnId.value
    })
    const col = columns.value.find(c => c.id === activeColumnId.value)
    if (col) col.tasks.push(data)
    showModal.value = false
  } catch (e) {
    alert('Error saving task.')
  }
}

const deleteTask = async (columnId, taskId) => {
  if (!confirm('Permanently delete this task?')) return
  try {
    await apiClient.delete(`/project-tasks/${taskId}`)
    const col = columns.value.find(c => c.id === columnId)
    if (col) col.tasks = col.tasks.filter(t => t.id !== taskId)
  } catch (e) {
    alert('Error deleting task.')
  }
}

const getPriorityClass = (priority) => ({
  'high':   'bg-danger/10 text-danger',
  'medium': 'bg-warning/10 text-warning',
  'low':    'bg-success/10 text-success'
})[priority] || 'bg-secondary/10 text-secondary'

const getPriorityLabel = (priority) => ({
  'high': 'High Risk', 'medium': 'Standard', 'low': 'Low Risk'
})[priority] || priority
</script>

<template>
  <div>
    <PageHeader title="Kanban Board" subtitle="Visualize your workflow">
      <template #actions>
        <button class="ti-btn ti-btn-primary" @click="openAddTask('pending')">
          <i class="ri-add-line me-1"></i> Add Task
        </button>
      </template>
    </PageHeader>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12 text-textmuted">
      <i class="ri-loader-4-line animate-spin me-2 text-2xl"></i> Loading board...
    </div>

    <div v-else class="flex gap-6 overflow-x-auto pb-4">
      <div v-for="column in columns" :key="column.id" class="flex-shrink-0 w-80">
        <div class="box h-full">
          <div class="box-header flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full" :class="`bg-${column.color}`"></span>
              <h6 class="box-title mb-0">{{ column.title }}</h6>
            </div>
            <span class="badge bg-light text-defaulttextcolor">{{ column.tasks.length }}</span>
          </div>

          <div class="box-body space-y-3 min-h-[400px] bg-light/50">
            <div
              v-for="task in column.tasks"
              :key="task.id"
              class="bg-white p-3 rounded-lg shadow-sm border cursor-move hover:shadow-md transition-shadow group"
            >
              <div class="flex justify-between items-start gap-2">
                <h6 class="font-medium mb-1 text-sm leading-snug flex-1">{{ task.title }}</h6>
                <button
                  class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-danger shrink-0"
                  @click="deleteTask(column.id, task.id)"
                  title="Delete task"
                >
                  <i class="ri-delete-bin-line text-xs"></i>
                </button>
              </div>
              <div class="flex items-center justify-between text-sm mt-3">
                <span class="text-xs text-textmuted">
                  <i class="ri-calendar-line me-1"></i>
                  {{ task.due_date ? new Date(task.due_date).toLocaleDateString('en-US', {month:'short',day:'numeric'}) : 'No due date' }}
                </span>
                <span class="badge" :class="getPriorityClass(task.priority)">
                  {{ getPriorityLabel(task.priority) }}
                </span>
              </div>
              <div v-if="task.priority === 'high' && column.id !== 'completed'" class="mt-2 pt-2 border-t text-xs text-danger flex items-center gap-1">
                <i class="ri-error-warning-line"></i> High priority — needs attention
              </div>
            </div>

            <!-- Add Task button per column -->
            <button
              class="w-full py-2 border-2 border-dashed rounded-lg text-textmuted hover:text-primary hover:border-primary transition-colors text-sm"
              @click="openAddTask(column.id)"
            >
              <i class="ri-add-line me-1"></i> Add Task
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Task Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-800">Add Task to <span class="text-indigo-600">{{ columns.find(c => c.id === activeColumnId)?.title }}</span></h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Task Title <span class="text-danger">*</span></label>
            <input
              v-model="modalForm.title"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
              placeholder="e.g. Implement API validation"
              @keyup.enter="saveTask"
            >
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
              <select v-model="modalForm.priority" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
              <input v-model="modalForm.due_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
          <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
          <button @click="saveTask" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 transition-colors shadow-sm">Add Task</button>
        </div>
      </div>
    </div>
  </div>
</template>
