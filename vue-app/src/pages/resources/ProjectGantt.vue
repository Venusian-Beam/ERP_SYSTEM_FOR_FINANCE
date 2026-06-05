<script setup>
import { ref, onMounted } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import { resourcesService } from '@/services/resourcesService'

const tasks = ref([])
const weeks = ref(['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8'])

onMounted(async () => {
  try {
    const data = await resourcesService.milestones({ type: 'gantt' })
    tasks.value = data.records || data
  } catch (e) {
    console.error('Failed to load gantt tasks:', e)
  }
})
</script>

<template>
  <div>
    <PageHeader title="Gantt Chart" subtitle="Visual project timeline">
      <template #actions>
        <button class="ti-btn ti-btn-light" @click="console.log('Zoom In not implemented')">
          <i class="ri-zoom-in-line me-1"></i> Zoom In
        </button>
        <button class="ti-btn ti-btn-light" @click="console.log('Zoom Out not implemented')">
          <i class="ri-zoom-out-line me-1"></i> Zoom Out
        </button>
        <button class="ti-btn ti-btn-primary" @click="console.log('Export not implemented')">
          <i class="ri-download-line me-1"></i> Export
        </button>
      </template>
    </PageHeader>

    <div class="box">
      <div class="box-body overflow-x-auto">
        <div class="min-w-[900px]">
          <!-- Header -->
          <div class="flex border-b">
            <div class="w-48 p-3 font-medium bg-light">Task Name</div>
            <div class="flex-1 flex">
              <div v-for="week in weeks" :key="week" class="flex-1 p-3 text-center text-sm text-textmuted border-l bg-light">
                {{ week }}
              </div>
            </div>
          </div>

          <!-- Tasks -->
          <div v-for="task in tasks" :key="task.id" class="flex border-b hover:bg-light/50">
            <div class="w-48 p-3 flex items-center gap-2">
              <span class="w-3 h-3 rounded" :class="task.color"></span>
              <span class="text-sm">{{ task.name }}</span>
            </div>
            <div class="flex-1 relative h-12">
              <div 
                class="absolute top-2 h-8 rounded flex items-center px-2 text-xs text-white"
                :class="task.color"
                :style="{
                  left: (task.start / 16 * 100) + '%',
                  width: (task.duration / 16 * 100) + '%'
                }"
              >
                {{ task.progress }}%
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Legend -->
    <div class="flex gap-4 mt-4">
      <div class="flex items-center gap-2">
        <span class="w-4 h-4 rounded bg-success"></span>
        <span class="text-sm text-textmuted">Completed</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-4 h-4 rounded bg-primary"></span>
        <span class="text-sm text-textmuted">In Progress</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-4 h-4 rounded bg-secondary"></span>
        <span class="text-sm text-textmuted">Not Started</span>
      </div>
    </div>
  </div>
</template>

