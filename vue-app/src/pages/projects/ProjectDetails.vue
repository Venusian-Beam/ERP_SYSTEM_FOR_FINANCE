<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageHeader from '@/components/ui/PageHeader.vue'
import { projectsService } from '@/services/projectsService'
import apiClient from '@/utils/apiClient'

const route = useRoute()
const router = useRouter()
const projectId = route.params.id

const project = ref(null)
const tasks = ref([])
const loading = ref(true)
const activeTab = ref('overview')

const showAddTaskModal = ref(false)
const newTask = ref({
  title: '',
  assignee: '',
  status: 'pending'
})

const fetchProject = async () => {
  try {
    const { data } = await apiClient.get(`/projects/${projectId}`)
    project.value = data.record || data
  } catch (e) {
    console.error('Failed to load project:', e)
  }
}

const fetchTasks = async () => {
  try {
    const { data } = await apiClient.get('/project-tasks', { project_id: projectId })
    tasks.value = data
  } catch (e) {
    console.error('Failed to load tasks:', e)
  }
}

onMounted(async () => {
  await fetchProject()
  await fetchTasks()
  loading.value = false
})

const navigateToStakeholders = () => {
  router.push(`/initiation/stakeholders?projectId=${projectId}`)
}

const openAddTaskModal = () => {
  activeTab.value = 'tasks'
  showAddTaskModal.value = true
}

const closeAddTaskModal = () => {
  showAddTaskModal.value = false
  newTask.value = {
    title: '',
    assignee: '',
    status: 'pending'
  }
}

const saveTask = async () => {
  if (!newTask.value.title.trim()) {
    return
  }

  try {
    const { data } = await apiClient.post('/project-tasks', {
      ...newTask.value,
      project_id: projectId
    })
    tasks.value.push(data)
    closeAddTaskModal()
  } catch (e) {
    alert('Error saving task.')
  }
}

const formatCurrency = (amount) => {
  if (!amount) return 'GHC 0'
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'GHS', maximumFractionDigits: 0 }).format(amount)
}

const formatDate = (dateStr) => {
  if (!dateStr) return '\u2014'
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
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
</script>

<template>
  <div>
    <div v-if="loading" class="flex justify-center items-center h-[400px]">
      <div class="flex flex-col items-center gap-4">
        <i class="ri-loader-4-line animate-spin text-4xl text-primary"></i>
        <span class="text-textmuted">Loading project...</span>
      </div>
    </div>

    <div v-else-if="!project" class="text-center py-12">
      <i class="ri-error-warning-line text-4xl text-danger mb-3"></i>
      <h3 class="text-lg font-medium mb-2">Project Not Found</h3>
      <p class="text-textmuted mb-4">The project you are looking for does not exist or has been removed.</p>
      <button class="ti-btn ti-btn-primary" @click="router.push('/projects')">Back to Projects</button>
    </div>

    <template v-else>
      <div class="box">
        <div class="box-body">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <h2 class="text-2xl font-semibold mb-1">{{ project.name }}</h2>
              <p v-if="project.description" class="text-textmuted">{{ project.description }}</p>
            </div>
            <div class="flex items-center gap-3">
              <span class="badge text-sm px-3 py-1.5" :class="getStatusClass(project.status)">
                {{ project.status }}
              </span>
              <span class="badge text-sm px-3 py-1.5" :class="getPriorityClass(project.priority)">
                {{ project.priority }}
              </span>
            </div>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div>
              <span class="text-xs text-textmuted">Budget</span>
              <p class="font-semibold mt-1">{{ formatCurrency(project.budget) }}</p>
            </div>
            <div>
              <span class="text-xs text-textmuted">Start Date</span>
              <p class="font-semibold mt-1">{{ formatDate(project.start_date) }}</p>
            </div>
            <div>
              <span class="text-xs text-textmuted">End Date</span>
              <p class="font-semibold mt-1">{{ formatDate(project.end_date) }}</p>
            </div>
            <div>
              <span class="text-xs text-textmuted">Tasks</span>
              <p class="font-semibold mt-1">{{ tasks.length }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="box mt-6">
        <div class="box-header">
          <div class="flex gap-4">
            <button
              class="ti-btn"
              :class="activeTab === 'overview' ? 'ti-btn-primary' : 'ti-btn-light'"
              @click="activeTab = 'overview'"
            >Overview</button>
            <button
              class="ti-btn"
              :class="activeTab === 'tasks' ? 'ti-btn-primary' : 'ti-btn-light'"
              @click="activeTab = 'tasks'"
            >Tasks ({{ tasks.length }})</button>
          </div>
        </div>

        <div v-if="activeTab === 'overview'" class="box-body">
          <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 lg:col-span-6">
              <h6 class="font-medium mb-3">Project Details</h6>
              <dl class="space-y-3">
                <div class="flex justify-between">
                  <dt class="text-textmuted">Status</dt>
                  <dd>
                    <span class="badge" :class="getStatusClass(project.status)">{{ project.status }}</span>
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-textmuted">Priority</dt>
                  <dd>
                    <span class="badge" :class="getPriorityClass(project.priority)">{{ project.priority }}</span>
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-textmuted">Budget</dt>
                  <dd class="font-medium">{{ formatCurrency(project.budget) }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-textmuted">Start Date</dt>
                  <dd>{{ formatDate(project.start_date) }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-textmuted">End Date</dt>
                  <dd>{{ formatDate(project.end_date) }}</dd>
                </div>
              </dl>
            </div>
            <div class="col-span-12 lg:col-span-6">
              <h6 class="font-medium mb-3">Quick Actions</h6>
              <div class="flex flex-wrap gap-2">
                <button class="ti-btn ti-btn-outline ti-btn-sm" @click="navigateToStakeholders">
                  <i class="ri-team-line me-1"></i> Stakeholders
                </button>
                <button class="ti-btn ti-btn-outline ti-btn-sm" @click="openAddTaskModal">
                  <i class="ri-add-line me-1"></i> Add Task
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="activeTab === 'tasks'" class="box-body p-0">
          <div v-if="tasks.length === 0" class="text-center py-8">
            <i class="ri-task-line text-3xl text-textmuted mb-2"></i>
            <p class="text-textmuted">No tasks yet.</p>
            <button class="ti-btn ti-btn-primary mt-3" @click="openAddTaskModal">
              <i class="ri-add-line me-1"></i> Add Task
            </button>
          </div>
          <table v-else class="table table-hover whitespace-nowrap table-standard">
            <thead>
              <tr>
                <th>Title</th>
                <th>Assignee</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="task in tasks" :key="task.id">
                <td class="font-medium">{{ task.title }}</td>
                <td>{{ task.assignee || '\u2014' }}</td>
                <td>
                  <span class="badge" :class="getStatusClass(task.status)">{{ task.status }}</span>
                </td>
                <td>
                  <button class="ti-btn ti-btn-soft-info ti-btn-icon ti-btn-sm">
                    <i class="ri-edit-line"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <div
      v-if="showAddTaskModal"
      class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40"
    >
      <div class="bg-white dark:bg-bgdark rounded-xl shadow-xl w-full max-w-lg mx-4">
        <div class="px-6 py-4 border-b border-defaultborder/60 flex items-center justify-between">
          <h3 class="text-base font-semibold">Add Task</h3>
          <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="closeAddTaskModal">
            <i class="ri-close-line"></i>
          </button>
        </div>
        <div class="px-6 py-5 space-y-4">
          <div>
            <label class="ti-form-label text-sm mb-1">Title <span class="text-danger">*</span></label>
            <input v-model="newTask.title" type="text" class="ti-form-control" placeholder="Enter task title">
          </div>
          <div>
            <label class="ti-form-label text-sm mb-1">Assignee</label>
            <input v-model="newTask.assignee" type="text" class="ti-form-control" placeholder="Assignee name">
          </div>
          <div>
            <label class="ti-form-label text-sm mb-1">Status</label>
            <select v-model="newTask.status" class="ti-form-select">
              <option value="pending">Pending</option>
              <option value="in-progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light/40 dark:bg-bgdark/40 rounded-b-xl">
          <button class="ti-btn ti-btn-light" type="button" @click="closeAddTaskModal">Cancel</button>
          <button class="ti-btn ti-btn-primary" type="button" :disabled="!newTask.title.trim()" @click="saveTask">Save Task</button>
        </div>
      </div>
    </div>
  </div>
</template>
