<script setup>
import { ref, nextTick, onBeforeUnmount, onMounted } from 'vue'
import axios from 'axios'
import ApexCharts from 'apexcharts'
import PageHeader from '@/components/ui/PageHeader.vue'
import AiAssistant from '@/components/finance/AiAssistant.vue'

let charts = []
const dashboardData = ref({
  revenue: 0,
  expenses: 0,
  active_invoices: [],
  pending_bills: []
})
const loading = ref(true)

onMounted(async () => {
  try {
    const response = await axios.get('/api/dashboard')
    dashboardData.value = response.data
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  } finally {
    loading.value = false
  }
  
  nextTick(() => {
    initializeCharts()
  })
})

onBeforeUnmount(() => {
  charts.forEach((chart) => chart?.destroy?.())
  charts = []
})

const initializeCharts = () => {
  const getPrimaryColor = () => {
    const root = document.documentElement
    const primaryRgb = getComputedStyle(root).getPropertyValue('--primary-rgb').trim()
    return primaryRgb ? `rgb(${primaryRgb})` : 'rgb(92, 103, 247)'
  }

  const primaryColor = getPrimaryColor()

  const sparklineOptions = [
    { id: 'Kpi-1', color: primaryColor, data: [30, 42, 38, 55, 48, 62, 58, 70, 65, 78, 82, 90] },
    { id: 'Kpi-2', color: 'rgb(227, 84, 212)', data: [25, 28, 32, 30, 38, 42, 40, 48, 52, 55, 58, 62] },
    { id: 'Kpi-3', color: 'rgb(255, 93, 159)', data: [28, 32, 30, 35, 38, 36, 40, 39, 42, 41, 43, 43] },
    { id: 'Kpi-4', color: 'rgb(255, 142, 111)', data: [85, 88, 82, 90, 87, 92, 89, 86, 91, 88, 90, 89] },
  ]

  sparklineOptions.forEach((config) => {
    const element = document.querySelector(`#${config.id}`)
    if (!element) return

    const chart = new ApexCharts(element, {
      series: [{ data: config.data }],
      chart: {
        type: 'bar',
        width: 76,
        height: 42,
        sparkline: { enabled: true },
      },
      plotOptions: { bar: { columnWidth: '80%', borderRadius: 2 } },
      colors: [config.color],
      tooltip: {
        fixed: { enabled: false },
        x: { show: false },
        y: { title: { formatter: () => '' } },
      },
    })
    chart.render()
    charts.push(chart)
  })

  const financeStatsElement = document.querySelector('#finance-statistics')
  if (financeStatsElement) {
    const chart = new ApexCharts(financeStatsElement, {
      series: [
        { name: 'Expenses', type: 'area', data: [85, 92, 88, 105, 112, 118, 125, 130, 135, 142, 148, 155] },
        { name: 'Revenue', type: 'bar', data: [120, 140, 135, 165, 178, 195, 210, 225, 240, 258, 275, 290] },
      ],
      chart: {
        type: 'area',
        height: 338,
        animations: { speed: 500 },
        toolbar: { show: false },
        dropShadow: { enabled: true, top: 8, left: 0, blur: 4, color: '#000', opacity: 0.08 },
      },
      colors: ['rgb(227, 84, 212)', primaryColor],
      dataLabels: { enabled: false },
      grid: { borderColor: '#f1f1f1', strokeDashArray: 3 },
      fill: {
        type: ['gradient', 'solid'],
        gradient: { opacityFrom: 0.1, opacityTo: 0.2, shadeIntensity: 0.1 },
      },
      stroke: {
        curve: ['smooth', 'smooth'],
        width: [2, 1.5],
        dashArray: [4, 5],
      },
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        axisTicks: { show: false },
      },
      yaxis: {
        labels: { formatter: (value) => `$${value}K` },
      },
      legend: { show: true, position: 'bottom', inverseOrder: true },
      plotOptions: {
        bar: {
          columnWidth: '20%',
          borderRadius: 3,
          borderRadiusApplication: 'end',
          borderRadiusWhenStacked: 'last',
        },
      },
    })
    chart.render()
    charts.push(chart)
  }

  const budgetTargetElement = document.querySelector('#budget-target')
  if (budgetTargetElement) {
    const chart = new ApexCharts(budgetTargetElement, {
      series: [78, 65, 42],
      chart: { height: 238, type: 'radialBar' },
      plotOptions: {
        radialBar: {
          dataLabels: {
            name: { fontSize: '18px', offsetY: 0 },
            value: { fontSize: '13px', offsetY: 5 },
            total: {
              show: true,
              label: 'Used',
              formatter: () => '62%',
            },
          },
        },
      },
      stroke: { lineCap: 'round' },
      grid: { padding: { bottom: -10, top: -10 } },
      colors: [primaryColor, 'rgba(227, 84, 212, 0.7)', 'rgba(255, 93, 159, 0.6)'],
      labels: ['Operations', 'Payroll', 'Marketing'],
    })
    chart.render()
    charts.push(chart)
  }

  const cashFlowElement = document.querySelector('#cashflow-report')
  if (cashFlowElement) {
    const chart = new ApexCharts(cashFlowElement, {
      series: [
        { name: 'This Week', data: [44, 52, 67, 86, 78, 65, 90] },
        { name: 'Last Week', data: [34, 42, 52, 56, 51, 76, 60] },
      ],
      chart: { type: 'line', height: 238, toolbar: { show: false } },
      grid: { borderColor: '#f1f1f1', strokeDashArray: 3 },
      stroke: { width: 2, curve: 'smooth', dashArray: [0, 3] },
      colors: [primaryColor, 'rgb(227, 84, 212)'],
      dataLabels: { enabled: false },
      legend: { show: true, position: 'top' },
      tooltip: {
        enabled: true,
        theme: 'dark',
        y: { formatter: (value) => `$${value}K` },
      },
      yaxis: {
        labels: { formatter: (value) => `$${Number(value || 0).toFixed(0)}K` },
      },
      xaxis: {
        categories: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
        axisBorder: { show: true, color: 'rgba(119, 119, 142, 0.05)' },
        axisTicks: { show: true, borderType: 'solid', color: 'rgba(119, 119, 142, 0.05)', width: 6 },
      },
    })
    chart.render()
    charts.push(chart)
  }
}
</script>

<template>
  <div class="finance-dashboard">
    <PageHeader title="Financial Overview" subtitle="Real-time command center for company finances">
      <template #actions>
        <router-link class="ti-btn ti-btn-primary ti-btn-sm" to="/reports/profit-loss">
          View Reports
        </router-link>
      </template>
    </PageHeader>

    <section class="grid grid-cols-12 gap-6">
      <div class="xl:col-span-8 col-span-12">
        <div class="box main-dashboard-banner project-dashboard-banner overflow-hidden h-full" style="background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%);">
          <div class="box-body p-6 h-full">
            <div class="grid grid-cols-12 gap-6 items-center h-full">
              <div class="lg:col-span-7 col-span-12">
                <h4 class="mb-2 font-bold text-white text-2xl">Manage Finances</h4>
                <p class="mb-5 text-white/90 max-w-xl text-sm leading-relaxed">
                  Track cash flow, approve invoices, and monitor company financial health from one place. Get real-time insights and take action quickly.
                </p>
                <router-link class="ti-btn ti-btn-sm bg-white text-primary font-semibold hover:bg-slate-50 border-0" to="/receivables/invoices">
                  Manage Now
                  <i class="ti ti-arrow-narrow-right ms-1"></i>
                </router-link>
              </div>
              <div class="lg:col-span-5 col-span-12 hidden md:block text-end">
                <img alt="Finance illustration" class="img-fluid ms-auto max-h-[190px] object-contain drop-shadow-xl" src="@/assets/images/media-85.png" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="xl:col-span-4 col-span-12">
        <div class="box h-full">
          <div class="box-header">
            <div class="box-title">Cash Position</div>
          </div>
          <div class="box-body">
            <div class="flex items-start justify-between gap-4 mb-5">
              <div>
                <p class="text-textmuted mb-1">Available cash</p>
                <h3 class="text-2xl font-semibold tabular-nums mb-0">GHC 892K</h3>
              </div>
              <span class="avatar avatar-lg bg-primary/10 text-primary">
                <i class="ri-bank-line text-2xl"></i>
              </span>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div class="p-3 rounded-md bg-light">
                <p class="text-textmuted mb-1 text-xs">Receivables</p>
                <p class="font-semibold tabular-nums mb-0">GHC 214K</p>
              </div>
              <div class="p-3 rounded-md bg-light">
                <p class="text-textmuted mb-1 text-xs">Payables</p>
                <p class="font-semibold tabular-nums mb-0">GHC 96K</p>
              </div>
              <div class="p-3 rounded-md bg-light">
                <p class="text-textmuted mb-1 text-xs">Runway</p>
                <p class="font-semibold mb-0">8.4 mo</p>
              </div>
              <div class="p-3 rounded-md bg-light">
                <p class="text-textmuted mb-1 text-xs">Forecast</p>
                <p class="font-semibold text-success mb-0">Healthy</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Floating AI Widget -->
    <AiAssistant />

    <section class="grid grid-cols-12 gap-5 mt-6">
      <!-- KPI Card 1: Total Revenue -->
      <div class="xl:col-span-3 md:col-span-6 col-span-12">
        <div class="clean-kpi-card hover:shadow-lg transition-all duration-300 bg-white border border-gray-100 rounded-xl p-4 flex flex-col justify-between h-full relative overflow-hidden group">
          <div class="flex justify-between items-start mb-4">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-indigo-50 text-indigo-500 group-hover:scale-110 transition-transform">
              <i class="ri-line-chart-line text-xl"></i>
            </div>
            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-500">+12.40%</span>
          </div>
          <div class="flex justify-between items-end">
            <div>
              <p class="text-xs text-gray-500 mb-1 font-medium">Total Revenue</p>
              <h4 class="text-lg font-bold text-gray-800" v-if="!loading">GHC {{ dashboardData.revenue.toLocaleString() }}</h4>
              <h4 class="text-lg font-bold text-gray-800" v-else>Loading...</h4>
            </div>
            <svg class="w-16 h-8 opacity-80" viewBox="0 0 80 36" preserveAspectRatio="none">
              <rect v-for="(h,i) in [30,42,38,55,48,62,58,70,65,78]" :key="i" :x="i*8+1" :y="36-(h/78)*34" width="5" :height="(h/78)*34" rx="2" fill="#6366f1"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- KPI Card 2: Total Expenses -->
      <div class="xl:col-span-3 md:col-span-6 col-span-12">
        <div class="clean-kpi-card hover:shadow-lg transition-all duration-300 bg-white border border-gray-100 rounded-xl p-4 flex flex-col justify-between h-full relative overflow-hidden group">
          <div class="flex justify-between items-start mb-4">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-pink-50 text-pink-500 group-hover:scale-110 transition-transform">
              <i class="ri-arrow-down-circle-line text-xl"></i>
            </div>
            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-rose-50 text-rose-500">+4.80%</span>
          </div>
          <div class="flex justify-between items-end">
            <div>
              <p class="text-xs text-gray-500 mb-1 font-medium">Total Expenses</p>
              <h4 class="text-lg font-bold text-gray-800" v-if="!loading">GHC {{ dashboardData.expenses.toLocaleString() }}</h4>
              <h4 class="text-lg font-bold text-gray-800" v-else>Loading...</h4>
            </div>
            <svg class="w-16 h-8 opacity-80" viewBox="0 0 80 36" preserveAspectRatio="none">
              <rect v-for="(h,i) in [25,28,32,30,38,42,40,48,52,55]" :key="i" :x="i*8+1" :y="36-(h/55)*34" width="5" :height="(h/55)*34" rx="2" fill="#ec4899"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- KPI Card 3: Net Profit Margin -->
      <div class="xl:col-span-3 md:col-span-6 col-span-12">
        <div class="clean-kpi-card hover:shadow-lg transition-all duration-300 bg-white border border-gray-100 rounded-xl p-4 flex flex-col justify-between h-full relative overflow-hidden group">
          <div class="flex justify-between items-start mb-4">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-cyan-50 text-cyan-500 group-hover:scale-110 transition-transform">
              <i class="ri-percent-line text-xl"></i>
            </div>
            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-500">+3.20%</span>
          </div>
          <div class="flex justify-between items-end">
            <div>
              <p class="text-xs text-gray-500 mb-1 font-medium">Net Profit Margin</p>
              <h4 class="text-lg font-bold text-gray-800">42.96%</h4>
            </div>
            <svg class="w-16 h-8 opacity-80" viewBox="0 0 80 36" preserveAspectRatio="none">
              <rect v-for="(h,i) in [28,32,30,35,38,36,40,39,42,43]" :key="i" :x="i*8+1" :y="36-(h/43)*34" width="5" :height="(h/43)*34" rx="2" fill="#06b6d4"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- KPI Card 4: Cash on Hand -->
      <div class="xl:col-span-3 md:col-span-6 col-span-12">
        <div class="clean-kpi-card hover:shadow-lg transition-all duration-300 bg-white border border-gray-100 rounded-xl p-4 flex flex-col justify-between h-full relative overflow-hidden group">
          <div class="flex justify-between items-start mb-4">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-orange-50 text-orange-500 group-hover:scale-110 transition-transform">
              <i class="ri-bank-line text-xl"></i>
            </div>
            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-rose-50 text-rose-500">-2.10%</span>
          </div>
          <div class="flex justify-between items-end">
            <div>
              <p class="text-xs text-gray-500 mb-1 font-medium">Cash on Hand</p>
              <h4 class="text-lg font-bold text-gray-800">GHC 892K</h4>
            </div>
            <svg class="w-16 h-8 opacity-80" viewBox="0 0 80 36" preserveAspectRatio="none">
              <rect v-for="(h,i) in [85,88,82,90,87,92,89,86,91,92]" :key="i" :x="i*8+1" :y="36-(h/92)*34" width="5" :height="(h/92)*34" rx="2" fill="#f97316"/>
            </svg>
          </div>
        </div>
      </div>
    </section>

    <section class="grid grid-cols-12 gap-6 mt-6">
      <div class="xl:col-span-8 col-span-12">
        <div class="box h-full">
          <div class="box-header justify-between">
            <div>
              <div class="box-title">Revenue vs Expenses</div>
              <p class="text-textmuted text-xs mb-0 mt-1">Monthly trend across the current financial year</p>
            </div>
            <button class="ti-btn ti-btn-sm bg-light">
              This Year <i class="ri-arrow-down-s-line align-middle inline-block"></i>
            </button>
          </div>
          <div class="box-body">
            <div class="grid md:grid-cols-2 grid-cols-1 gap-4 mb-4">
              <div class="flex gap-4 items-center p-4 bg-light rounded-md">
                <div class="avatar avatar-lg flex-shrink-0 bg-primary/10 avatar-rounded shadow-sm border border-primary border-opacity-25">
                  <i class="ri-stack-line text-2xl text-primary"></i>
                </div>
                <div>
                  <span class="mb-1 block text-textmuted">Total Revenue</span>
                  <div class="flex items-end gap-2 flex-wrap">
                    <h4 class="mb-0 tabular-nums" v-if="!loading">GHC {{ dashboardData.revenue.toLocaleString() }}</h4>
                    <h4 class="mb-0 tabular-nums text-textmuted" v-else>Loading...</h4>
                    <span class="badge leading-none bg-success align-middle opacity-90">
                      5.6%<i class="ti ti-trending-up"></i>
                    </span>
                  </div>
                </div>
              </div>
              <div class="flex gap-4 items-center p-4 bg-light rounded-md">
                <div class="avatar avatar-lg flex-shrink-0 bg-primarytint1color/10 avatar-rounded shadow-sm border border-primarytint1color border-opacity-25">
                  <i class="ri-wallet-3-line text-2xl text-primarytint1color"></i>
                </div>
                <div>
                  <span class="mb-1 block text-textmuted">Total Expenses</span>
                  <div class="flex items-end gap-2 flex-wrap">
                    <h4 class="mb-0 tabular-nums" v-if="!loading">GHC {{ dashboardData.expenses.toLocaleString() }}</h4>
                    <h4 class="mb-0 tabular-nums text-textmuted" v-else>Loading...</h4>
                    <span class="badge leading-none bg-danger align-middle opacity-90">
                      1.6%<i class="ti ti-trending-down"></i>
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div id="finance-statistics"></div>
          </div>
        </div>
      </div>

      <div class="xl:col-span-4 col-span-12">
        <div class="box h-full">
          <div class="box-header justify-between">
            <div class="box-title">Budget Allocation</div>
            <router-link class="ti-btn ti-btn-sm bg-light" to="/resources/budget">View All</router-link>
          </div>
          <div class="box-body">
            <div id="budget-target"></div>
            <div class="grid grid-cols-3 gap-3 text-center p-4 bg-light rounded-md">
              <div>
                <span class="mb-1 block text-xs">
                  <i class="ri-circle-fill text-[8px] text-primary align-middle"></i>
                  Operations
                </span>
                <h6 class="mb-1 tabular-nums">GHC 485K</h6>
                <span class="text-success font-medium text-xs"><i class="ri-arrow-up-s-fill"></i>3.5%</span>
              </div>
              <div>
                <span class="mb-1 block text-xs">
                  <i class="ri-circle-fill text-[8px] text-primarytint1color align-middle"></i>
                  Payroll
                </span>
                <h6 class="mb-1 tabular-nums">GHC 649K</h6>
                <span class="text-danger font-medium text-xs"><i class="ri-arrow-down-s-fill"></i>1.5%</span>
              </div>
              <div>
                <span class="mb-1 block text-xs">
                  <i class="ri-circle-fill text-[8px] text-primarytint2color align-middle"></i>
                  Marketing
                </span>
                <h6 class="mb-1 tabular-nums">GHC 292K</h6>
                <span class="text-success font-medium text-xs"><i class="ri-arrow-up-s-fill"></i>0.1%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="grid grid-cols-12 gap-6 mt-6">
      <div class="xl:col-span-4 col-span-12">
        <div class="box h-full">
          <div class="box-header justify-between">
            <div class="box-title">Active Invoices</div>
            <router-link class="ti-btn ti-btn-sm bg-primary/10 text-primary" to="/receivables/invoices">View All</router-link>
          </div>
          <div class="box-body space-y-5">
            <div v-if="loading" class="text-center py-4 text-textmuted">Loading invoices...</div>
            <div v-else-if="dashboardData.active_invoices.length === 0" class="text-center py-4 text-textmuted">No active invoices</div>
            <div v-for="(invoice, index) in dashboardData.active_invoices" :key="invoice.id">
              <div class="flex items-start justify-between gap-4 mb-3">
                <div>
                  <p class="font-medium mb-1 text-[14px]">{{ invoice.invoice_number }} - {{ invoice.customer?.name }}</p>
                  <p class="text-textmuted mb-1 text-xs">Status: {{ invoice.status }}</p>
                  <span class="text-success font-normal text-xs">{{ Math.round((invoice.paid_amount / invoice.amount) * 100) || 0 }}% collected - GHC {{ invoice.amount.toLocaleString() }}</span>
                </div>
                <span class="text-[11px] text-textmuted whitespace-nowrap">Due: {{ new Date(invoice.due_date).toLocaleDateString() }}</span>
              </div>
              <div class="progress progress-lg !rounded-full p-1" :class="index % 2 === 0 ? 'bg-primary/10' : 'bg-primarytint1color/10'" role="progressbar" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="Math.round((invoice.paid_amount / invoice.amount) * 100)">
                <div class="progress-bar progress-bar-striped progress-bar-animated !rounded-full" :class="index % 2 !== 0 ? 'bg-primarytint1color' : ''" :style="`width: ${Math.round((invoice.paid_amount / invoice.amount) * 100)}%`"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="xl:col-span-4 col-span-12">
        <div class="box h-full">
          <div class="box-header justify-between">
            <div class="box-title">Pending Approvals</div>
            <router-link class="ti-btn ti-btn-sm bg-light" to="/payables/bills">View All</router-link>
          </div>
          <div class="box-body space-y-4">
            <div class="p-4 rounded-md bg-primary/10 border border-primary/20">
              <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                  <p class="font-medium mb-1">Invoice #INV-2841</p>
                  <p class="text-textmuted text-xs mb-0">CFO review required</p>
                </div>
                <span class="font-semibold tabular-nums">GHC 48,200</span>
              </div>
              <div class="flex flex-wrap gap-2">
                <span class="badge leading-none bg-primary/10 text-primary">AR</span>
                <span class="badge leading-none bg-danger/10 text-danger">High Priority</span>
                <span class="badge leading-none bg-info/10 text-info">09:15 AM</span>
              </div>
            </div>

            <div class="p-4 rounded-md bg-light">
              <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                  <p class="font-medium mb-1">Vendor bill #BILL-1198</p>
                  <p class="text-textmuted text-xs mb-0">Operations approval</p>
                </div>
                <span class="font-semibold tabular-nums">GHC 12,450</span>
              </div>
              <div class="flex flex-wrap gap-2">
                <span class="badge leading-none bg-warning/10 text-warning">AP</span>
                <span class="badge leading-none bg-primarytint1color/10 text-primarytint1color">Today</span>
              </div>
            </div>

            <div class="p-4 rounded-md bg-light">
              <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                  <p class="font-medium mb-1">Payroll batch</p>
                  <p class="text-textmuted text-xs mb-0">Final sign-off</p>
                </div>
                <span class="font-semibold tabular-nums">GHC 86,300</span>
              </div>
              <div class="flex flex-wrap gap-2">
                <span class="badge leading-none bg-success/10 text-success">Payroll</span>
                <span class="badge leading-none bg-info/10 text-info">Tomorrow</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="xl:col-span-4 col-span-12">
        <div class="box h-full">
          <div class="box-header">
            <div>
              <div class="box-title">Cash Flow Trend</div>
              <p class="text-textmuted text-xs mb-0 mt-1">This week compared with last week</p>
            </div>
          </div>
          <div class="box-body">
            <div id="cashflow-report"></div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
