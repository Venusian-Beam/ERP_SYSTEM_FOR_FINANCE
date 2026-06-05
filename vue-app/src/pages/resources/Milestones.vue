<script setup>
import { ref, onMounted } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import { resourcesService } from '@/services/resourcesService'

const milestones = ref([])

onMounted(async () => {
  try {
    const data = await resourcesService.milestones()
    milestones.value = data.records || data
  } catch (e) {
    console.error('Failed to load milestones:', e)
  }
})
</script>

<template>
  <div>
    <PageHeader title="Milestones" subtitle="Track project milestones and deliverables">
      <template #actions>
        <button class="ti-btn ti-btn-primary" @click="console.log('Add Milestone modal not implemented')">
          <i class="ri-add-line me-1"></i> Add Milestone
        </button>
      </template>
    </PageHeader>

    <div class="box">
      <div class="box-body">
        <div class="relative">
          <!-- Timeline -->
          <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
          
          <div class="space-y-8">
            <div v-for="milestone in milestones" :key="milestone.id" class="relative flex items-start gap-4 pl-8">
              <span class="absolute left-6 w-4 h-4 rounded-full border-2 border-white" :class="{
                'bg-success': milestone.status === 'completed',
                'bg-primary': milestone.status === 'in-progress',
                'bg-gray-300': milestone.status === 'upcoming'
              }"></span>
              <div class="flex-1 p-4 bg-light rounded-lg">
                <div class="flex items-center justify-between mb-2">
                  <h6 class="font-medium">{{ milestone.name }}</h6>
                  <span class="badge" :class="{
                    'bg-success/10 text-success': milestone.status === 'completed',
                    'bg-primary/10 text-primary': milestone.status === 'in-progress',
                    'bg-secondary/10 text-secondary': milestone.status === 'upcoming'
                  }">{{ milestone.status }}</span>
                </div>
                <div class="flex items-center gap-4 text-sm text-textmuted">
                  <span><i class="ri-calendar-line me-1"></i>{{ milestone.date }}</span>
                  <span><i class="ri-folder-line me-1"></i>{{ milestone.project }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

