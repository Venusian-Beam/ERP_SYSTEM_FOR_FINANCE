<script setup>
import { ref } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import apiClient from '@/utils/apiClient'

const reportTypes = ref([])

const selectedReport = ref('')
const dateRange = ref({ start: '', end: '' })
const processing = ref(false)

const submitCompile = async () => {
  processing.value = true
  try {
    await apiClient.post('/compliance/compile-archive', {
      report_type: selectedReport.value,
      date_start: dateRange.value.start,
      date_end: dateRange.value.end,
    })
    console.log('Archive compilation queued.')
  } catch (e) {
    console.error('Failed to compile archive:', e)
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader title="Reports & Analytics" subtitle="Generate and view project reports">
      <template #actions>
        <button class="ti-btn ti-btn-primary">
          <i class="ri-download-line me-1"></i> Export All
        </button>
      </template>
    </PageHeader>

    <div class="grid grid-cols-12 gap-6">
      <!-- Report Types -->
      <div class="col-span-12 xl:col-span-8">
        <div class="grid grid-cols-12 gap-4">
          <div v-for="report in reportTypes" :key="report.id" class="col-span-12 md:col-span-6 lg:col-span-4">
            <div class="box h-full cursor-pointer hover:shadow-lg transition-shadow" @click="selectedReport = report.name">
              <div class="box-body text-center">
                <span class="avatar avatar-lg mb-3" :class="`bg-${report.color}/10 text-${report.color}`">
                  <i :class="report.icon" class="text-2xl"></i>
                </span>
                <h6 class="font-medium mb-1">{{ report.name }}</h6>
                <p class="text-sm text-textmuted">{{ report.description }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Configuration -->
      <div class="col-span-12 xl:col-span-4">
        <div class="box">
          <div class="box-header">
            <h5 class="box-title">Generate Report</h5>
          </div>
          <div class="box-body">
            <div class="mb-4">
              <label class="ti-form-label">Report Type</label>
              <select v-model="selectedReport" class="ti-form-select">
                <option value="">Select a report</option>
                <option v-for="report in reportTypes" :key="report.id" :value="report.name">{{ report.name }}</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="ti-form-label">Date Range</label>
              <div class="grid grid-cols-2 gap-2">
                <input v-model="dateRange.start" type="date" class="ti-form-control" placeholder="Start">
                <input v-model="dateRange.end" type="date" class="ti-form-control" placeholder="End">
              </div>
            </div>
            <div class="mb-4">
              <label class="ti-form-label">Project</label>
              <select class="ti-form-select">
                <option>All Projects</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="ti-form-label">Format</label>
              <div class="flex gap-2">
                <button class="ti-btn ti-btn-light flex-1">PDF</button>
                <button class="ti-btn ti-btn-light flex-1">Excel</button>
                <button class="ti-btn ti-btn-primary flex-1">Preview</button>
              </div>
            </div>
            <button class="ti-btn ti-btn-primary w-full" @click="submitCompile" :disabled="processing">
              <span v-if="processing" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <i v-else class="ri-file-chart-line me-1"></i> 
              {{ processing ? 'Compiling Archive...' : 'Compile Compliance Package Archive' }}
            </button>
          </div>
        </div>

        <!-- Recent Reports -->
        <div class="box">
          <div class="box-header">
            <h5 class="box-title">Recent Reports</h5>
          </div>
          <div class="box-body">
            <p class="text-textmuted text-center py-4">No reports generated yet.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

