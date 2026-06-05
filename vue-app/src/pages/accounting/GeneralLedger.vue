<script setup>
import { ref, computed, onMounted } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import DateRangePicker from '@/components/ui/DateRangePicker.vue'
import { formatCurrency, formatDate } from '@/utils/formatters'
import { exportToCSV } from '@/utils/exportUtils'
import { accountingService } from '@/services/accountingService'

// State
const dateRange = ref({ from: '2024-11-01', to: '2024-11-30' })
const selectedAccount = ref('all')
const loading = ref(false)
const accounts = ref([])
const ledgerData = ref([])

onMounted(async () => {
  await fetchLedger()
})

async function fetchLedger() {
  loading.value = true
  try {
    const params = { from: dateRange.value.from, to: dateRange.value.to }
    if (selectedAccount.value !== 'all') params.account = selectedAccount.value
    const data = await accountingService.request('general-ledger', params)
    ledgerData.value = data.records || []
  } catch (e) {
    console.error('Failed to load general ledger:', e)
  } finally {
    loading.value = false
  }
}

const filteredLedger = computed(() => {
  if (selectedAccount.value === 'all') return ledgerData.value
  return ledgerData.value.filter(a => a.accountCode === selectedAccount.value)
})

const exportExcel = () => {
  loading.value = true
  
  // Flatten ledger transactions for export
  const flatData = []
  filteredLedger.value.forEach(acc => {
    acc.transactions.forEach(trx => {
      flatData.push({
        account: `${acc.accountCode} - ${acc.accountName}`,
        date: trx.date,
        reference: trx.ref,
        description: trx.desc,
        debit: trx.debit || 0,
        credit: trx.credit || 0
      })
    })
  })
  
  const columns = [
    { label: 'Account', key: 'account' },
    { label: 'Date', key: 'date' },
    { label: 'Reference', key: 'reference' },
    { label: 'Description', key: 'description' },
    { label: 'Debit', key: 'debit', type: 'money' },
    { label: 'Credit', key: 'credit', type: 'money' }
  ]
  
  exportToCSV(flatData, columns, 'General_Ledger_Export.csv')
  loading.value = false
}

const printReport = () => {
  window.print()
}
</script>

<template>
  <div>
    <!-- Header -->
    <PageHeader
      title="General Ledger"
      subtitle="Detailed history of all posted transactions across your charts of accounts."
    >
      <template #actions>
        <button class="ti-btn btn-outline" @click="exportExcel" :disabled="loading">
          <i class="ri-file-excel-2-line" v-if="!loading"></i>
          <i class="ri-loader-4-line spin" v-else></i>
          Export to Excel
        </button>
        <button class="ti-btn btn-gradient" @click="printReport">
          <i class="ri-printer-line"></i> Print
        </button>
      </template>
    </PageHeader>

    <!-- Filters Box -->
    <div class="box filter-box">
      <div class="box-body">
        <div class="filters-wrap">
          <!-- Date Filter -->
          <div class="filter-item">
            <label class="filter-lbl">Date Range</label>
            <DateRangePicker v-model="dateRange" label="" />
          </div>

          <!-- Account Filter -->
          <div class="filter-item">
            <label class="filter-lbl">Account</label>
            <select v-model="selectedAccount" class="filter-select">
              <option value="all">All Accounts</option>
              <optgroup label="Assets">
                <option v-for="acc in accounts.filter(a => a.type === 'Asset')" :key="acc.code" :value="acc.code">
                  {{ acc.code }} — {{ acc.name }}
                </option>
              </optgroup>
              <optgroup label="Liabilities">
                <option v-for="acc in accounts.filter(a => a.type === 'Liability')" :key="acc.code" :value="acc.code">
                  {{ acc.code }} — {{ acc.name }}
                </option>
              </optgroup>
              <optgroup label="Revenue">
                <option v-for="acc in accounts.filter(a => a.type === 'Revenue')" :key="acc.code" :value="acc.code">
                  {{ acc.code }} — {{ acc.name }}
                </option>
              </optgroup>
              <optgroup label="Expense">
                <option v-for="acc in accounts.filter(a => a.type === 'Expense')" :key="acc.code" :value="acc.code">
                  {{ acc.code }} — {{ acc.name }}
                </option>
              </optgroup>
            </select>
          </div>

          <div class="filter-item filter-apply">
            <button class="ti-btn btn-primary-soft" @click="fetchLedger" :disabled="loading">
              <i class="ri-filter-3-line" v-if="!loading"></i>
              <i class="ri-loader-4-line spin" v-else></i>
              Filter
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Ledger Content -->
    <div class="gl-content">
      <div v-if="filteredLedger.length === 0" class="empty-state box">
        <div class="empty-icon">
          <i class="ri-book-3-line"></i>
        </div>
        <p class="empty-title">No transactions found</p>
        <p class="empty-sub">There are no posted transactions for the selected criteria.</p>
      </div>

      <template v-else>
        <!-- One box per account -->
        <div class="box account-box" v-for="acc in filteredLedger" :key="acc.accountCode">
          <!-- Account Header -->
          <div class="account-header">
            <div class="acc-title-wrap">
              <span class="acc-code">{{ acc.accountCode }}</span>
              <h3 class="acc-name">{{ acc.accountName }}</h3>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table-standard">
              <thead>
                <tr>
                  <th style="width:120px">Date</th>
                  <th style="width:150px">Reference</th>
                  <th>Description</th>
                  <th class="text-end" style="width:140px">Debit (DR)</th>
                  <th class="text-end" style="width:140px">Credit (CR)</th>
                </tr>
              </thead>
              <tbody>
                <!-- Beginning Balance -->
                <tr class="balance-row beg-bal">
                  <td colspan="3" class="text-end font-medium">Beginning Balance</td>
                  <td class="text-end font-semibold">
                    <span v-if="acc.beginningBalance > 0">{{ formatCurrency(acc.beginningBalance) }}</span>
                  </td>
                  <td class="text-end font-semibold">
                    <span v-if="acc.beginningBalance < 0">{{ formatCurrency(Math.abs(acc.beginningBalance)) }}</span>
                  </td>
                </tr>

                <!-- Transactions -->
                <tr v-for="trx in acc.transactions" :key="trx.id" class="trx-row">
                  <td class="text-muted-sm">{{ formatDate(trx.date) }}</td>
                  <td><span class="ref-badge">{{ trx.ref }}</span></td>
                  <td class="trx-desc">{{ trx.desc }}</td>
                  <td class="text-end tabular-nums">
                    <span v-if="trx.debit > 0" class="text-success">{{ formatCurrency(trx.debit) }}</span>
                    <span v-else class="text-muted">—</span>
                  </td>
                  <td class="text-end tabular-nums">
                    <span v-if="trx.credit > 0" class="text-danger">{{ formatCurrency(trx.credit) }}</span>
                    <span v-else class="text-muted">—</span>
                  </td>
                </tr>

                <!-- Transaction lines empty -->
                <tr v-if="!acc.transactions || acc.transactions.length === 0">
                  <td colspan="5" class="text-center text-muted py-4">No activity in this period.</td>
                </tr>

                <!-- Net Change (optional intermediate row) -->
                <tr class="balance-row net-change">
                  <td colspan="3" class="text-end font-medium text-muted">Net Change for Period</td>
                  <td class="text-end font-medium text-success">
                    <span v-if="acc.netChange > 0">{{ formatCurrency(acc.netChange) }}</span>
                  </td>
                  <td class="text-end font-medium text-danger">
                    <span v-if="acc.netChange < 0">{{ formatCurrency(Math.abs(acc.netChange)) }}</span>
                  </td>
                </tr>

                <!-- Ending Balance -->
                <tr class="balance-row end-bal">
                  <td colspan="3" class="text-end font-bold">Ending Balance</td>
                  <td class="text-end font-bold text-lg">
                    <span v-if="acc.endingBalance > 0">{{ formatCurrency(acc.endingBalance) }}</span>
                  </td>
                  <td class="text-end font-bold text-lg">
                    <span v-if="acc.endingBalance < 0">{{ formatCurrency(Math.abs(acc.endingBalance)) }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<style scoped>
/* ─── Buttons ─────────────────────────────────────────────── */
.btn-gradient {
  background: linear-gradient(135deg, var(--primary), var(--primarytint1color));
  color: white;
  border: none;
  box-shadow: 0 4px 10px rgba(92, 103, 247, 0.25);
}

.btn-gradient:hover { filter: brightness(1.1); }

.btn-outline {
  background: transparent;
  color: var(--primary);
  border: 1.5px solid var(--primary);
}

.btn-outline:hover { background: rgba(92, 103, 247, 0.08); }

.btn-primary-soft {
  background: rgba(92, 103, 247, 0.1);
  color: var(--primary);
  border: none;
}
.btn-primary-soft:hover { background: var(--primary); color: white; }

.ti-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all 0.2s ease;
}

.spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* ─── Filters ─────────────────────────────────────────────── */
.filter-box { margin-bottom: 1.5rem; }
.filters-wrap {
  display: flex;
  gap: 1.5rem;
  align-items: flex-end;
  flex-wrap: wrap;
}

.filter-item {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.filter-apply {
  margin-left: auto;
}

.filter-lbl {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.filter-select {
  height: 2.5rem;
  padding: 0 1rem;
  border: 1.5px solid var(--border-default);
  border-radius: var(--radius-md);
  background: var(--bg-card);
  color: var(--text-default);
  font-size: 0.875rem;
  min-width: 240px;
  outline: none;
  transition: border-color var(--transition-fast);
}

.filter-select:focus {
  border-color: var(--primary);
}

/* ─── GL Tables ───────────────────────────────────────────── */
.gl-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.account-box {
  overflow: hidden;
}

.account-header {
  background: rgba(99,102,241,0.03);
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border-default);
}

.acc-title-wrap {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.acc-code {
  font-family: 'Courier New', monospace;
  font-size: 1rem;
  font-weight: 700;
  color: var(--primary);
  background: rgba(99,102,241,0.1);
  padding: 0.25rem 0.5rem;
  border-radius: var(--radius-sm);
}

.acc-name {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text-heading);
}

.trx-row:hover td { background: rgba(99,102,241,0.02); }

.trx-desc {
  font-weight: 500;
  color: var(--text-heading);
}

.ref-badge {
  font-size: 0.7rem;
  background: var(--bg-app);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-sm);
  padding: 0.15rem 0.4rem;
  font-weight: 600;
  color: var(--text-muted);
}

.text-muted-sm { font-size: 0.75rem; color: var(--text-muted); }

/* Balances */
.balance-row td { background: var(--bg-app); }
.beg-bal td { border-bottom: 2px solid var(--border-strong); }
.net-change td { border-bottom: 1px dashed var(--border-default); }
.end-bal td { border-bottom: none; border-top: 2px solid var(--border-strong); }

.text-lg { font-size: 1rem; }

/* ─── Empty State ─────────────────────────────────────────── */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 4rem 2rem;
  gap: 0.5rem;
}

.empty-icon {
  width: 4rem;
  height: 4rem;
  background: rgba(99,102,241,0.08);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  color: var(--primary);
  margin-bottom: 0.5rem;
}

.empty-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-heading);
}

.empty-sub {
  font-size: 0.8125rem;
  color: var(--text-muted);
}

@media print {
  .app-header, .app-sidebar, .filter-box, .ti-btn { display: none !important; }
  .box { box-shadow: none; border: 1px solid #ddd; page-break-inside: avoid; }
}
</style>
