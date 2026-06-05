<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import PageHeader from '@/components/ui/PageHeader.vue'
import { exportToCSV, exportToPDF } from '@/utils/exportUtils'

const props = defineProps({
  title: String,
  subtitle: String,
  actionLabel: { type: String, default: 'Create New' },
  actionIcon: { type: String, default: 'ri-add-line' },
  metrics: { type: Array, default: () => [] },
  columns: { type: Array, default: () => [] },
  records: { type: Array, default: () => [] },
  filters: { type: Array, default: () => [] },
  detailBase: String,
  insight: Object,
  currentPage: { type: Number, default: 1 },
  totalPages: { type: Number, default: 0 },
  totalRecords: { type: Number, default: 0 },
  loading: { type: Boolean, default: false },
  currency: { type: String, default: 'GHC' },
})

const emit = defineEmits(['primary-action', 'edit-action', 'delete-action', 'next-page', 'prev-page', 'go-to-page'])

const router = useRouter()
const search = ref('')
const selectedFilter = ref('all')
const showExportMenu = ref(false)

const handleExportCSV = () => {
  const filename = `${props.title.replace(/\s+/g, '_').toLowerCase()}_export.csv`
  exportToCSV(filteredRecords.value, props.columns, filename)
  showExportMenu.value = false
}

const handleExportPDF = () => {
  const filename = `${props.title.replace(/\s+/g, '_').toLowerCase()}_report.pdf`
  exportToPDF(filteredRecords.value, props.columns, `${props.title} Report`, filename)
  showExportMenu.value = false
}

const filteredRecords = computed(() => {
  const query = search.value.trim().toLowerCase()
  return props.records.filter((record) => {
    const matchesSearch = !query || Object.values(record).some(value =>
      String(value).toLowerCase().includes(query)
    )
    const matchesFilter = selectedFilter.value === 'all' ||
      Object.values(record).some(value => String(value).toLowerCase() === selectedFilter.value.toLowerCase())
    return matchesSearch && matchesFilter
  })
})

// Unique vibrant gradient palettes — no two the same, all theme-matching
const cardPalettes = [
  { gradient: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', shadow: 'rgba(102,126,234,0.38)', iconBg: 'rgba(255,255,255,0.22)' },
  { gradient: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)', shadow: 'rgba(240,147,251,0.38)', iconBg: 'rgba(255,255,255,0.22)' },
  { gradient: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)', shadow: 'rgba(79,172,254,0.38)', iconBg: 'rgba(255,255,255,0.22)' },
  { gradient: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)', shadow: 'rgba(250,112,154,0.38)', iconBg: 'rgba(255,255,255,0.22)' },
  { gradient: 'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)', shadow: 'rgba(161,140,209,0.38)', iconBg: 'rgba(255,255,255,0.22)' },
  { gradient: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)', shadow: 'rgba(67,233,123,0.38)', iconBg: 'rgba(255,255,255,0.22)' },
]

const isMoney = (column) => column.type === 'money'
const money = (value) => props.currency + ' ' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value || 0))

const statusClass = (value) => {
  const status = String(value).toLowerCase()
  if (['paid', 'active', 'matched', 'approved', 'current', 'completed'].includes(status)) return 'success'
  if (['overdue', 'inactive', 'failed', 'rejected', 'high risk'].includes(status)) return 'danger'
  if (['pending', 'open', 'scheduled', 'review', 'partial'].includes(status)) return 'warning'
  return 'neutral'
}

const openRecord = (record) => {
  if (props.detailBase && record.id) router.push(`${props.detailBase}/${record.id}`)
}

const sparkDataSets = [
  [30, 42, 38, 55, 48, 62, 58, 70, 65, 78],
  [25, 28, 32, 30, 38, 42, 40, 48, 52, 55],
  [28, 32, 30, 35, 38, 36, 40, 39, 42, 43],
  [85, 88, 82, 90, 87, 92, 89, 86, 91, 92],
  [42, 38, 45, 50, 47, 55, 52, 60, 58, 65],
  [60, 55, 62, 58, 65, 70, 67, 74, 72, 78],
]
const maxOf = (arr) => Math.max(...arr)
</script>

<template>
  <section class="finance-workspace">
    <PageHeader :title="title" :subtitle="subtitle">
      <template #actions>
        <div class="relative">
          <button class="secondary-btn" @click="showExportMenu = !showExportMenu">
            <i class="ri-download-2-line"></i> Export <i class="ri-arrow-down-s-line ml-1"></i>
          </button>
          <div v-if="showExportMenu" class="absolute right-0 mt-1 w-36 bg-white border border-gray-200 rounded-md shadow-lg z-10">
            <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2" @click="handleExportCSV">
              <i class="ri-file-excel-2-line text-emerald-600"></i> CSV
            </button>
            <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2" @click="handleExportPDF">
              <i class="ri-file-pdf-2-line text-rose-600"></i> PDF
            </button>
          </div>
        </div>
        <button class="primary-btn" @click="$emit('primary-action')"><i :class="actionIcon"></i> {{ actionLabel }}</button>
      </template>
    </PageHeader>

    <!-- ── Metric Cards ── -->
    <div class="metric-grid">
      <article
        v-for="(metric, idx) in metrics"
        :key="metric.label"
        class="metric-card"
        :style="{
          background: cardPalettes[idx % cardPalettes.length].gradient,
          boxShadow: `0 8px 28px ${cardPalettes[idx % cardPalettes.length].shadow}`,
        }"
      >
        <!-- Shine overlay -->
        <div class="mc-shine"></div>

        <!-- Top row: icon + trend badge -->
        <div class="mc-top">
          <div class="mc-icon" :style="{ background: cardPalettes[idx % cardPalettes.length].iconBg }">
            <i :class="metric.icon"></i>
          </div>
          <span class="mc-badge" :class="metric.trend?.startsWith('-') ? 'mc-badge-down' : 'mc-badge-up'">
            {{ metric.trend }}
          </span>
        </div>

        <!-- Bottom row: label + value / sparkline -->
        <div class="mc-bottom">
          <div class="mc-text">
            <p class="mc-label">{{ metric.label }}</p>
            <strong class="mc-value">{{ metric.value }}</strong>
          </div>

          <!-- SVG Sparkline bar chart -->
          <svg class="mc-spark" viewBox="0 0 80 36" preserveAspectRatio="none">
            <rect
              v-for="(val, i) in sparkDataSets[idx % sparkDataSets.length]"
              :key="i"
              :x="i * 8 + 1"
              :y="36 - (val / maxOf(sparkDataSets[idx % sparkDataSets.length])) * 34"
              width="5"
              :height="(val / maxOf(sparkDataSets[idx % sparkDataSets.length])) * 34"
              rx="2"
              fill="rgba(255,255,255,0.82)"
            />
          </svg>
        </div>
      </article>
    </div>

    <!-- ── Insight Banner ── -->
    <article v-if="insight" class="insight-card">
      <div class="insight-icon"><i class="ri-information-line"></i></div>
      <div>
        <strong>{{ insight.title }}</strong>
        <p>{{ insight.text }}</p>
      </div>
      <button>Review details <i class="ri-arrow-right-line"></i></button>
    </article>

    <!-- ── Data Table ── -->
    <div class="data-card">
      <div class="toolbar">
        <label class="search-field">
          <i class="ri-search-line"></i>
          <input v-model="search" :placeholder="`Search ${title?.toLowerCase()}...`">
        </label>
        <div class="toolbar-actions">
          <select v-if="filters.length" v-model="selectedFilter">
            <option value="all">All records</option>
            <option v-for="filter in filters" :key="filter" :value="filter">{{ filter }}</option>
          </select>
          <button class="filter-btn"><i class="ri-filter-3-line"></i> More filters</button>
        </div>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th v-for="column in columns" :key="column.key" :class="{ right: isMoney(column) }">{{ column.label }}</th>
              <th class="right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="record in filteredRecords" :key="record.id || record.reference" class="table-row">
              <td v-for="column in columns" :key="column.key" :class="{ right: isMoney(column), strong: column.primary }">
                <span v-if="column.type === 'status'" class="status-pill" :class="statusClass(record[column.key])">
                  {{ record[column.key] }}
                </span>
                <span v-else-if="isMoney(column)" class="money">{{ money(record[column.key]) }}</span>
                <span v-else>{{ record[column.key] }}</span>
              </td>
              <td class="right">
                <button class="icon-btn" title="View details" @click="openRecord(record)"><i class="ri-eye-line"></i></button>
                <button class="icon-btn" title="Edit" @click="$emit('edit-action', record)"><i class="ri-edit-line"></i></button>
                <button class="icon-btn" title="Delete" @click="$emit('delete-action', record)"><i class="ri-delete-bin-line"></i></button>
              </td>
            </tr>
            <tr v-if="!filteredRecords.length">
              <td :colspan="columns.length + 1" class="empty">No records match your filters.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <footer class="table-footer">
        <span>{{ loading ? 'Loading...' : `Showing ${filteredRecords.length} of ${totalRecords || records.length} records` }}</span>
        <div>
          <button :disabled="currentPage <= 1" @click="$emit('prev-page')"><i class="ri-arrow-left-s-line"></i></button>
          <template v-for="p in Math.min(totalPages || 1, 5)" :key="p">
            <button v-if="totalPages > 0" :class="{ current: p === currentPage }" @click="$emit('go-to-page', p)">{{ p }}</button>
          </template>
          <button v-if="totalPages > 5" disabled>...</button>
          <button :disabled="currentPage >= totalPages" @click="$emit('next-page')"><i class="ri-arrow-right-s-line"></i></button>
        </div>
      </footer>
    </div>
  </section>
</template>

<style scoped>
/* ── Layout ──────────────────────────────────────────── */
.finance-workspace {
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
  width: 100%;
  max-width: 1240px;
  margin: 0 auto;
}

/* ── Metric Grid ─────────────────────────────────────── */
.metric-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(180px, 1fr));
  gap: 1rem;
}

/* ── Metric Card ─────────────────────────────────────── */
.metric-card {
  border-radius: 1rem;
  padding: 1.1rem 1.15rem 0.95rem;
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
  cursor: default;
  position: relative;
  overflow: hidden;
  border: none;
  transition: transform 0.25s cubic-bezier(.34,1.56,.64,1), box-shadow 0.25s ease;
}

.metric-card:hover {
  transform: translateY(-6px) scale(1.025);
}

/* Glossy shine */
.mc-shine {
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 55%;
  background: linear-gradient(180deg, rgba(255,255,255,0.14) 0%, transparent 100%);
  pointer-events: none;
  border-radius: 1rem 1rem 0 0;
}

/* ── Card Top ────────────────────────────────────────── */
.mc-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  z-index: 1;
}

.mc-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.6rem;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(6px);
  font-size: 1.15rem;
  color: #fff;
}

.mc-badge {
  font-size: 0.67rem;
  font-weight: 700;
  padding: 0.22rem 0.58rem;
  border-radius: 99px;
  letter-spacing: 0.01em;
}
.mc-badge-up   { background: rgba(255,255,255,0.28); color: #fff; }
.mc-badge-down { background: rgba(220,30,60,0.38);   color: #fff; }

/* ── Card Bottom ─────────────────────────────────────── */
.mc-bottom {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 0.5rem;
  position: relative;
  z-index: 1;
}

.mc-label {
  font-size: 0.72rem;
  margin: 0 0 0.28rem 0;
  font-weight: 500;
  color: rgba(255,255,255,0.78);
}

.mc-value {
  font-size: 1.18rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  display: block;
  line-height: 1.2;
  color: #fff;
}

/* ── Sparkline ───────────────────────────────────────── */
.mc-spark {
  width: 72px;
  height: 36px;
  flex-shrink: 0;
  opacity: 0.88;
}

/* ── Insight ─────────────────────────────────────────── */
.data-card, .insight-card {
  background: var(--bg-card, #fff);
  border: 1px solid rgba(var(--primary-rgb), 0.12);
  border-radius: 0.65rem;
  box-shadow: 0 1px 3px rgba(15,23,42,.06);
}

.insight-card {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  max-width: 820px;
  padding: 0.85rem 1rem;
}

.insight-icon {
  width: 2rem;
  height: 2rem;
  border-radius: 0.4rem;
  display: grid;
  place-items: center;
  background: rgba(var(--primary-rgb), 0.1);
  color: var(--primary);
  flex-shrink: 0;
}

.insight-card strong { font-size: 0.78rem; color: #0f172a; }
.insight-card p { font-size: 0.71rem; color: var(--text-muted, #64748b); margin: 0.1rem 0 0; }
.insight-card button {
  margin-left: auto;
  border: 0;
  background: transparent;
  color: var(--primary);
  font-size: 0.71rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: opacity 0.15s;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}
.insight-card button:hover { opacity: 0.75; }

/* ── Buttons ─────────────────────────────────────────── */
.primary-btn, .secondary-btn, .filter-btn {
  border-radius: 0.4rem;
  padding: 0.46rem 0.78rem;
  font-size: 0.73rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}
.primary-btn {
  border: 1px solid var(--primary);
  color: #fff;
  background: var(--primary);
}
.primary-btn:hover { opacity: 0.88; transform: translateY(-1px); }
.secondary-btn, .filter-btn {
  border: 1px solid var(--border-default, #e2e8f0);
  color: #475569;
  background: #fff;
}
.secondary-btn:hover, .filter-btn:hover {
  color: var(--primary);
  border-color: rgba(var(--primary-rgb), 0.3);
  background: rgba(var(--primary-rgb), 0.04);
}

/* ── Toolbar ─────────────────────────────────────────── */
.toolbar {
  display: flex;
  justify-content: space-between;
  gap: 0.8rem;
  padding: 0.82rem 1rem;
  border-bottom: 1px solid var(--border-default, #e2e8f0);
}
.search-field { position: relative; width: 260px; max-width: 100%; }
.search-field i { position: absolute; left: 0.72rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem; }
.search-field input, .toolbar select {
  height: 2.15rem;
  border: 1px solid var(--border-default, #e2e8f0);
  border-radius: 0.4rem;
  background: var(--bg-card, #fff);
  color: var(--text-default, #334155);
  font-size: 0.73rem;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.search-field input { width: 100%; padding: 0 0.7rem 0 2.2rem; }
.search-field input:focus, .toolbar select:focus {
  outline: none;
  border-color: rgba(var(--primary-rgb), 0.45);
  box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.08);
}
.toolbar-actions { display: flex; gap: 0.4rem; }
.toolbar select { min-width: 140px; padding: 0 0.6rem; }

/* ── Table ───────────────────────────────────────────── */
.table-wrap { overflow: auto; }
table { width: 100%; border-collapse: collapse; }
th {
  padding: 0.65rem 1rem;
  text-align: left;
  font-size: 0.63rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: #64748b;
  background: #f8fafc;
  border-bottom: 1px solid var(--border-default, #e2e8f0);
  white-space: nowrap;
}
td {
  padding: 0.76rem 1rem;
  font-size: 0.74rem;
  color: var(--text-default, #475569);
  border-bottom: 1px solid var(--border-default, #e2e8f0);
  white-space: nowrap;
}
.table-row { transition: background 0.13s; }
.table-row:hover { background: rgba(var(--primary-rgb), 0.04); }

.right { text-align: right; }
.strong { font-weight: 600; color: var(--text-heading, #0f172a); }

.status-pill {
  display: inline-flex;
  padding: 0.18rem 0.52rem;
  border-radius: 0.3rem;
  font-size: 0.64rem;
  font-weight: 600;
}
.success { background: rgba(16,185,129,.1); color: #059669; }
.danger  { background: rgba(244,63,94,.1);  color: #e11d48; }
.warning { background: rgba(245,158,11,.12); color: #d97706; }
.neutral { background: #f1f5f9; color: #64748b; }

.icon-btn {
  width: 1.72rem;
  height: 1.72rem;
  border: 1px solid var(--border-default, #e2e8f0);
  border-radius: 0.35rem;
  background: #fff;
  color: var(--primary);
  cursor: pointer;
  margin-left: 0.2rem;
  transition: all 0.15s;
}
.icon-btn:hover {
  background: rgba(var(--primary-rgb), 0.08);
  border-color: rgba(var(--primary-rgb), 0.3);
  transform: scale(1.1);
}

.empty { text-align: center; padding: 2.5rem; color: #94a3b8; font-size: 0.8rem; }

.table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.7rem 1rem;
  font-size: 0.7rem;
  color: #94a3b8;
}
.table-footer button {
  width: 1.72rem;
  height: 1.72rem;
  border: 1px solid var(--border-default, #e2e8f0);
  background: var(--bg-card, #fff);
  color: #64748b;
  cursor: pointer;
  border-radius: 0.3rem;
  transition: all 0.15s;
}
.table-footer button:hover:not(:disabled) { border-color: var(--primary); color: var(--primary); }
.table-footer .current { background: var(--primary); color: #fff; border-color: var(--primary); }

/* ── Responsive ──────────────────────────────────────── */
@media (max-width: 1000px) { .metric-grid { grid-template-columns: repeat(2, minmax(180px, 1fr)); } }
@media (max-width: 640px) {
  .metric-grid { grid-template-columns: 1fr; }
  .toolbar { flex-direction: column; }
  .search-field { width: 100%; }
  .insight-card { flex-direction: column; align-items: stretch; }
  .insight-card button { margin-left: 0; }
}
</style>
