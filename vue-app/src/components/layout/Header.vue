<script setup>
import { onMounted, onBeforeUnmount, nextTick, ref } from 'vue'
import ApexCharts from 'apexcharts'
import PageHeader from '@/components/ui/PageHeader.vue'

let charts = []
const searchQuery = ref('')
const isProfileDropdownOpen = ref(false)

const toggleProfileDropdown = () => {
  isProfileDropdownOpen.value = !isProfileDropdownOpen.value
}

onMounted(() => {
  nextTick(() => {
    initializeCharts()
  })
})

onBeforeUnmount(() => {
  charts.forEach((c) => c?.destroy?.())
  charts = []
})

const initializeCharts = () => {
  const getPrimaryColor = () => {
    const root = document.documentElement
    const primaryRgb = getComputedStyle(root).getPropertyValue('--primary-rgb').trim()
    return primaryRgb ? `rgb(${primaryRgb})` : 'rgb(92, 103, 247)'
  }

  const primaryColor = getPrimaryColor()

  // Sparkline charts for KPI cards
  const sparklineOptions = [
    { id: 'Kpi-1', color: primaryColor, data: [30, 42, 38, 55, 48, 62, 58, 70, 65, 78, 82, 90] },        // Revenue
    { id: 'Kpi-2', color: 'rgb(227, 84, 212)', data: [25, 28, 32, 30, 38, 42, 40, 48, 52, 55, 58, 62] }, // Expenses
    { id: 'Kpi-3', color: 'rgb(255, 93, 159)',
      data: [20, 22, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70] }, // Profit
  ]

  sparklineOptions.forEach((opt) => {
    const chart = new ApexCharts(document.querySelector(`#${opt.id}`), {
      chart: {
        type: 'area',
        height: 35,
        sparkline: { enabled: true },
        toolbar: { show: false },
      },
      stroke: { curve: 'smooth', width: 2 },
      fill: { opacity: 0.3 },
      colors: [opt.color],
      series: [{ name: opt.id, data: opt.data }],
      tooltip: { enabled: false },
    })
    chart.render()
    charts.push(chart)
  })
}
</script>

<template>
  <div class="app-header sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm" id="header">
    <div class="flex items-center justify-between px-6 py-3">
      <!-- Header Content Left -->
      <div class="flex items-center gap-6">
        <div class="header-element">
          <router-link class="header-logo" to="/">
            <img alt="KEDEBАН ERP Logo" class="h-8 w-auto object-contain" src="@/assets/images/Kedebah Logo.png"/>
          </router-link>
        </div>

        <div class="header-element flex items-center">
          <button aria-label="Toggle Sidebar" class="text-slate-500 hover:text-primary transition-colors text-xl p-2 rounded-md hover:bg-slate-100" @click="$emit('toggle-sidebar')">
            <i class="ri-menu-2-line"></i>
          </button>
        </div>

        <div class="header-element relative hidden md:block w-64">
          <input v-model="searchQuery" class="form-control pl-10 pr-4 py-2 w-full border-slate-200 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Search anything here..." type="text"/>
          <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        </div>
      </div>

      <!-- Header Content Right -->
      <div class="flex items-center gap-4">
        <!-- Notifications -->
        <div class="relative">
          <button class="text-slate-500 hover:text-primary transition-colors p-2 rounded-full hover:bg-slate-100 block">
            <i class="ri-notification-3-line text-xl"></i>
            <span class="absolute top-1 right-1 w-2 h-2 bg-pink-500 rounded-full border border-white"></span>
          </button>
        </div>

        <!-- Profile -->
        <div class="relative group">
          <button class="flex items-center gap-2 cursor-pointer p-1 rounded-lg hover:bg-slate-50 transition-colors" id="headerProfileDropdown" @click="toggleProfileDropdown">
            <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-semibold shadow-sm">PM</span>
            <div class="hidden sm:block text-left">
              <p class="text-sm font-semibold text-slate-700 leading-none mb-1">Project Manager</p>
              <p class="text-xs text-slate-500 leading-none">Admin</p>
            </div>
            <i class="ri-arrow-down-s-line text-slate-400 ms-1 text-sm"></i>
          </a>
          
          <ul v-show="isProfileDropdownOpen" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-100 py-2 z-50">
            <li><router-link class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary transition-colors" to="/settings/users"><i class="ri-user-line text-lg"></i> Profile</router-link></li>
            <li><router-link class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary transition-colors" to="/settings/preferences"><i class="ri-settings-3-line text-lg"></i> Settings</router-link></li>
            <li class="border-t border-slate-100 mt-2 pt-2"><router-link class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors" to="/login"><i class="ri-logout-box-line text-lg"></i> Log Out</router-link></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Scoped styles replaced with Tailwind utilities in template */
</style>
