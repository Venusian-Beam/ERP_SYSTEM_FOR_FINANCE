<script setup>
import { computed } from 'vue'
import { useCurrency } from '@/composables/useCurrency'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  // 'currency' | 'percent' | 'number' | 'compact' | 'raw'
  valueType: {
    type: String,
    default: 'currency'
  },
  change: {
    type: Number,
    default: null
  },
  // If true, a negative change is GOOD (e.g. expenses going down)
  inverseChange: {
    type: Boolean,
    default: false
  },
  changePeriod: {
    type: String,
    default: 'vs last month'
  },
  icon: {
    type: String,
    default: 'ri-bar-chart-line'
  },
  // 'primary' | 'success' | 'danger' | 'warning' | 'info'
  color: {
    type: String,
    default: 'primary'
  },
  // Optional sparkline chart element id
  chartId: {
    type: String,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  },
  // Optional subtitle below value
  subtitle: {
    type: String,
    default: null
  }
})

const { format, compact, percent } = useCurrency()

const formattedValue = computed(() => {
  if (props.loading) return '—'
  switch (props.valueType) {
    case 'currency': return format(props.value)
    case 'compact':  return compact(props.value)
    case 'percent':  return `${props.value}%`
    case 'number':   return Number(props.value).toLocaleString()
    case 'raw':      return props.value
    default:         return props.value
  }
})

const changeIsPositive = computed(() => {
  if (props.change === null) return null
  return props.inverseChange
    ? props.change <= 0
    : props.change >= 0
})

const changeIcon = computed(() => {
  if (props.change === null) return null
  return props.change >= 0
    ? 'ri-arrow-up-s-fill'
    : 'ri-arrow-down-s-fill'
})

const changeClass = computed(() => {
  if (props.change === null) return ''
  if (changeIsPositive.value) return 'change-positive'
  return 'change-negative'
})

const colorMap = {
  primary: {
    bg:   'rgba(99, 102, 241, 0.1)',
    text: 'var(--primary)',
    glow: 'rgba(99, 102, 241, 0.25)'
  },
  success: {
    bg:   'rgba(16, 185, 129, 0.1)',
    text: 'var(--finance-income)',
    glow: 'rgba(16, 185, 129, 0.25)'
  },
  danger: {
    bg:   'rgba(244, 63, 94, 0.1)',
    text: 'var(--finance-expense)',
    glow: 'rgba(244, 63, 94, 0.25)'
  },
  warning: {
    bg:   'rgba(245, 158, 11, 0.1)',
    text: 'var(--finance-pending)',
    glow: 'rgba(245, 158, 11, 0.25)'
  },
  info: {
    bg:   'rgba(59, 130, 246, 0.1)',
    text: 'var(--finance-info)',
    glow: 'rgba(59, 130, 246, 0.25)'
  }
}

const colorStyle = computed(() => colorMap[props.color] || colorMap.primary)
</script>

<template>
  <div class="kpi-card" :class="{ 'kpi-loading': loading }">
    <!-- Top Row: Icon + Change Badge -->
    <div class="kpi-top">
      <div
        class="kpi-icon-wrap"
        :style="{
          background: colorStyle.bg,
          boxShadow: `0 4px 12px ${colorStyle.glow}`
        }"
      >
        <i
          :class="icon"
          :style="{ color: colorStyle.text }"
        ></i>
      </div>

      <!-- Change Badge -->
      <div
        v-if="change !== null && !loading"
        class="kpi-change-badge"
        :class="changeClass"
      >
        <i :class="changeIcon"></i>
        {{ Math.abs(change).toFixed(1) }}%
      </div>

      <!-- Skeleton badge -->
      <div v-else-if="loading" class="skeleton skeleton-badge"></div>
    </div>

    <!-- Middle: Value + Label -->
    <div class="kpi-body">
      <!-- Skeleton state -->
      <template v-if="loading">
        <div class="skeleton skeleton-label"></div>
        <div class="skeleton skeleton-value"></div>
      </template>

      <template v-else>
        <div class="kpi-label">{{ title }}</div>
        <div class="kpi-value">{{ formattedValue }}</div>
        <div v-if="subtitle" class="kpi-subtitle">{{ subtitle }}</div>
      </template>
    </div>

    <!-- Bottom: Sparkline + Period -->
    <div class="kpi-bottom">
      <span v-if="changePeriod && !loading" class="kpi-period">
        {{ changePeriod }}
      </span>
      <div v-if="chartId" :id="chartId" class="kpi-sparkline"></div>
    </div>
  </div>
</template>

<style scoped>
/* ─── Card Shell ──────────────────────────────────────────── */
.kpi-card {
  background: var(--bg-card);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  padding: 1.25rem;
  box-shadow: var(--shadow-card);
  transition: box-shadow var(--transition-base),
              transform var(--transition-base);
  cursor: default;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  height: 100%;
}

.kpi-card:hover {
  box-shadow: 0 4px 20px rgba(99, 102, 241, 0.1);
  transform: translateY(-2px);
}

/* ─── Top Row ─────────────────────────────────────────────── */
.kpi-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.kpi-icon-wrap {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  flex-shrink: 0;
  transition: box-shadow var(--transition-base);
}

/* ─── Change Badge ────────────────────────────────────────── */
.kpi-change-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.15rem;
  padding: 0.2rem 0.5rem;
  border-radius: 99px;
  font-size: 0.7rem;
  font-weight: 700;
}

.change-positive {
  background: rgba(16, 185, 129, 0.1);
  color: var(--finance-income);
}

.change-negative {
  background: rgba(244, 63, 94, 0.1);
  color: var(--finance-expense);
}

/* ─── Body ────────────────────────────────────────────────── */
.kpi-body {
  flex: 1;
}

.kpi-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.35rem;
}

.kpi-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-heading);
  font-variant-numeric: tabular-nums;
  font-feature-settings: "tnum" 1;
  line-height: 1.2;
}

.kpi-subtitle {
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-top: 0.25rem;
}

/* ─── Bottom ──────────────────────────────────────────────── */
.kpi-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.kpi-period {
  font-size: 0.7rem;
  color: var(--text-muted);
}

.kpi-sparkline {
  flex-shrink: 0;
}

/* ─── Loading Skeletons ───────────────────────────────────── */
.kpi-loading {
  pointer-events: none;
}

.skeleton {
  background: linear-gradient(
    90deg,
    var(--border-default) 25%,
    var(--bg-app) 50%,
    var(--border-default) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: var(--radius-sm);
}

.skeleton-badge {
  width: 60px;
  height: 22px;
  border-radius: 99px;
}

.skeleton-label {
  width: 80px;
  height: 12px;
  margin-bottom: 0.5rem;
}

.skeleton-value {
  width: 120px;
  height: 32px;
}

@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
