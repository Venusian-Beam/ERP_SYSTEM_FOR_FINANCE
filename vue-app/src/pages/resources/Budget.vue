<script setup>
import { ref, onMounted } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import { resourcesService } from '@/services/resourcesService'

const budgetData = ref({ totalBudget: 0, spent: 0, remaining: 0, projected: 0 })
const budgetItems = ref([])

onMounted(async () => {
  try {
    const data = await resourcesService.budget()
    const d = data.record || data
    budgetData.value = {
      totalBudget: d.total_budget || d.totalBudget || 0,
      spent: d.spent || 0,
      remaining: d.remaining || 0,
      projected: d.projected || 0
    }
    budgetItems.value = d.items || d.categories || []
  } catch (e) {
    console.error('Failed to load budget:', e)
  }
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'GHS', maximumFractionDigits: 0 }).format(amount)
}
</script>

<template>
  <div>
    <PageHeader title="Budget Management" subtitle="Track and manage project budgets">
      <template #actions>
        <button class="ti-btn ti-btn-light" @click="console.log('Export Report not implemented')">
          <i class="ri-download-line me-1"></i> Export Report
        </button>
        <button class="ti-btn ti-btn-primary" @click="console.log('Add Expense modal not implemented')">
          <i class="ri-add-line me-1"></i> Add Expense
        </button>
      </template>
    </PageHeader>

    <div class="grid grid-cols-12 gap-6">
      <!-- Budget Overview -->
      <div class="col-span-12 md:col-span-6 xl:col-span-3">
        <div class="box">
          <div class="box-body">
            <div class="flex items-center gap-4">
              <span class="avatar avatar-lg bg-primary/10 text-primary">
                <i class="ri-money-dollar-circle-line text-2xl"></i>
              </span>
              <div>
                <p class="text-textmuted text-sm">Total Budget</p>
                <h4 class="text-xl font-bold">{{ formatCurrency(budgetData.totalBudget) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-span-12 md:col-span-6 xl:col-span-3">
        <div class="box">
          <div class="box-body">
            <div class="flex items-center gap-4">
              <span class="avatar avatar-lg bg-warning/10 text-warning">
                <i class="ri-shopping-cart-line text-2xl"></i>
              </span>
              <div>
                <p class="text-textmuted text-sm">Spent</p>
                <h4 class="text-xl font-bold">{{ formatCurrency(budgetData.spent) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-span-12 md:col-span-6 xl:col-span-3">
        <div class="box">
          <div class="box-body">
            <div class="flex items-center gap-4">
              <span class="avatar avatar-lg bg-success/10 text-success">
                <i class="ri-wallet-line text-2xl"></i>
              </span>
              <div>
                <p class="text-textmuted text-sm">Remaining</p>
                <h4 class="text-xl font-bold">{{ formatCurrency(budgetData.remaining) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-span-12 md:col-span-6 xl:col-span-3">
        <div class="box">
          <div class="box-body">
            <div class="flex items-center gap-4">
              <span class="avatar avatar-lg bg-info/10 text-info">
                <i class="ri-line-chart-line text-2xl"></i>
              </span>
              <div>
                <p class="text-textmuted text-sm">Projected</p>
                <h4 class="text-xl font-bold">{{ formatCurrency(budgetData.projected) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Budget Breakdown -->
      <div class="col-span-12">
        <div class="box">
          <div class="box-header">
            <h5 class="box-title">Budget Breakdown by Category</h5>
          </div>
          <div class="box-body p-0">
            <table class="table table-hover whitespace-nowrap table-standard">
              <thead>
                <tr>
                  <th>Category</th>
                  <th>Allocated</th>
                  <th>Spent</th>
                  <th>Progress</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in budgetItems" :key="item.id">
                  <td class="font-medium">{{ item.category }}</td>
                  <td>{{ formatCurrency(item.allocated) }}</td>
                  <td>{{ formatCurrency(item.spent) }}</td>
                  <td>
                    <div class="flex items-center gap-2 min-w-[150px]">
                      <div class="progress progress-sm flex-1">
                        <div class="progress-bar" :class="{
                          'bg-success': item.status === 'under',
                          'bg-primary': item.status === 'on-track',
                          'bg-danger': item.status === 'over'
                        }" :style="{ width: (item.spent / item.allocated) * 100 + '%' }"></div>
                      </div>
                      <span class="text-xs">{{ Math.round((item.spent / item.allocated) * 100) }}%</span>
                    </div>
                  </td>
                  <td>
                    <span class="badge" :class="{
                      'bg-success/10 text-success': item.status === 'under',
                      'bg-primary/10 text-primary': item.status === 'on-track',
                      'bg-danger/10 text-danger': item.status === 'over'
                    }">{{ item.status }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

