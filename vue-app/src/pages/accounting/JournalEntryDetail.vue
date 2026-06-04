<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageHeader from '@/components/ui/PageHeader.vue'
import StatusBadge from '@/components/finance/StatusBadge.vue'
import { formatCurrency, formatDate } from '@/utils/formatters'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const entry = ref(null)

// Mock fetch based on ID
onMounted(() => {
  // Simulate API call
  setTimeout(() => {
    entry.value = {
      id: route.params.id || 1,
      entryNo: `JE-${String(route.params.id || 1).padStart(4, '0')}`,
      date: '2024-11-14',
      description: 'Q4 Software License Purchase',
      reference: 'INV-2045',
      status: 'posted',
      createdBy: 'Patrick M.',
      createdAt: '2024-11-14T09:30:00Z',
      postedBy: 'Sarah J.',
      postedAt: '2024-11-14T11:45:00Z',
      totalDebit: 2400.00,
      totalCredit: 2400.00,
      notes: 'Annual renewal for Adobe Creative Cloud and Microsoft 365 licenses for the design and engineering teams.',
      lines: [
        { accountCode: '5300', accountName: 'Software & SaaS', description: 'Design Team Licenses', debit: 1200, credit: 0 },
        { accountCode: '5300', accountName: 'Software & SaaS', description: 'Engineering Team Licenses', debit: 1200, credit: 0 },
        { accountCode: '1010', accountName: 'Business Checking', description: 'Payment via corporate card', debit: 0, credit: 2400 }
      ],
      attachments: [
        { id: 1, name: 'adobe_invoice_nov24.pdf', size: '1.2 MB' },
        { id: 2, name: 'ms_receipt.png', size: '450 KB' }
      ]
    }
    loading.value = false
  }, 600)
})

const goBack = () => {
  router.push('/accounting/journal-entries')
}

const printEntry = () => {
  window.print()
}

const voidEntry = () => {
  if (confirm('Are you sure you want to void this entry? This action cannot be undone.')) {
    entry.value.status = 'voided'
  }
}
</script>

<template>
  <div>
    <PageHeader
      title="Journal Entry Detail"
      :subtitle="entry ? `Viewing ${entry.entryNo}` : 'Loading...'"
    >
      <template #actions>
        <button class="ti-btn btn-ghost" @click="goBack">
          <i class="ri-arrow-left-line"></i> Back to List
        </button>
        <button v-if="entry && entry.status !== 'voided'" class="ti-btn btn-outline" @click="voidEntry">
          <i class="ri-close-circle-line"></i> Void
        </button>
        <button class="ti-btn btn-gradient" @click="printEntry">
          <i class="ri-printer-line"></i> Print
        </button>
      </template>
    </PageHeader>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading entry details...</p>
    </div>

    <div v-else-if="entry" class="detail-grid">
      <!-- Left Column: Lines & Info -->
      <div class="main-col">
        <!-- Header Info Card -->
        <div class="box info-card">
          <div class="box-body">
            <div class="entry-header">
              <div class="entry-title-wrap">
                <h2 class="entry-title">{{ entry.description }}</h2>
                <StatusBadge :status="entry.status" size="md" />
              </div>
              <div class="entry-no-badge">{{ entry.entryNo }}</div>
            </div>

            <p v-if="entry.notes" class="entry-notes">
              <i class="ri-file-text-line text-muted"></i>
              {{ entry.notes }}
            </p>
          </div>
        </div>

        <!-- Lines Table -->
        <div class="box">
          <div class="box-header justify-between">
            <div class="box-title">Transaction Lines</div>
          </div>
          <div class="box-body p-0">
            <div class="table-responsive">
              <table class="je-table">
                <thead>
                  <tr>
                    <th>Account</th>
                    <th>Description</th>
                    <th class="text-end">Debit (DR)</th>
                    <th class="text-end">Credit (CR)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(line, idx) in entry.lines" :key="idx" :class="line.debit > 0 ? 'line-debit' : 'line-credit'">
                    <td>
                      <div class="account-info">
                        <span class="account-code">{{ line.accountCode }}</span>
                        <span class="account-name">{{ line.accountName }}</span>
                      </div>
                    </td>
                    <td>
                      <span class="line-desc">{{ line.description || '—' }}</span>
                    </td>
                    <td class="text-end tabular-nums">
                      <span v-if="line.debit > 0" class="text-success font-medium">
                        {{ formatCurrency(line.debit) }}
                      </span>
                      <span v-else class="text-muted">—</span>
                    </td>
                    <td class="text-end tabular-nums">
                      <span v-if="line.credit > 0" class="text-danger font-medium">
                        {{ formatCurrency(line.credit) }}
                      </span>
                      <span v-else class="text-muted">—</span>
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="lines-total-row">
                    <td colspan="2" class="text-end font-semibold">Totals:</td>
                    <td class="text-end tabular-nums text-success font-bold text-lg">
                      {{ formatCurrency(entry.totalDebit) }}
                    </td>
                    <td class="text-end tabular-nums text-danger font-bold text-lg">
                      {{ formatCurrency(entry.totalCredit) }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Meta & Attachments -->
      <div class="side-col">
        <!-- Details Box -->
        <div class="box">
          <div class="box-header">
            <div class="box-title">Entry Details</div>
          </div>
          <div class="box-body">
            <div class="meta-list">
              <div class="meta-item">
                <span class="meta-label">Date</span>
                <span class="meta-val font-medium">{{ formatDate(entry.date) }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-label">Reference</span>
                <span class="meta-val badge-ref">{{ entry.reference || 'None' }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-label">Created By</span>
                <span class="meta-val">{{ entry.createdBy }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-label">Created At</span>
                <span class="meta-val text-muted-sm">{{ formatDate(entry.createdAt, true) }}</span>
              </div>
              <div class="meta-separator"></div>
              <div class="meta-item">
                <span class="meta-label">Posted By</span>
                <span class="meta-val">{{ entry.postedBy || '—' }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-label">Posted At</span>
                <span class="meta-val text-muted-sm">{{ entry.postedAt ? formatDate(entry.postedAt, true) : '—' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Attachments Box -->
        <div class="box">
          <div class="box-header justify-between">
            <div class="box-title">Attachments</div>
            <button class="ti-btn btn-ghost btn-sm">
              <i class="ri-upload-cloud-2-line"></i>
            </button>
          </div>
          <div class="box-body">
            <div v-if="entry.attachments && entry.attachments.length" class="attachments-list">
              <div v-for="att in entry.attachments" :key="att.id" class="attachment-card">
                <div class="att-icon">
                  <i class="ri-file-pdf-line" v-if="att.name.endsWith('.pdf')"></i>
                  <i class="ri-image-line" v-else></i>
                </div>
                <div class="att-info">
                  <p class="att-name">{{ att.name }}</p>
                  <p class="att-size">{{ att.size }}</p>
                </div>
                <button class="att-dl">
                  <i class="ri-download-2-line"></i>
                </button>
              </div>
            </div>
            <div v-else class="no-attachments">
              <i class="ri-attachment-line"></i>
              <p>No attachments uploaded</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.detail-grid {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 992px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }
}

/* ─── Loading State ───────────────────────────────────────── */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  color: var(--text-muted);
}

.spinner {
  width: 2rem;
  height: 2rem;
  border: 3px solid rgba(99,102,241,0.2);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

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
  color: var(--finance-expense);
  border: 1.5px solid var(--finance-expense);
}

.btn-outline:hover { background: rgba(244,63,94,0.08); }

.btn-ghost {
  background: transparent;
  color: var(--text-muted);
  border: none;
}

.btn-ghost:hover {
  background: rgba(0, 0, 0, 0.05);
  color: var(--text-default);
}

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

.btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }

/* ─── Info Card ───────────────────────────────────────────── */
.info-card .box-body { padding: 1.5rem; }

.entry-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.entry-title-wrap {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.entry-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-heading);
  margin: 0;
}

.entry-no-badge {
  font-family: 'Courier New', monospace;
  font-size: 1rem;
  font-weight: 700;
  color: var(--primary);
  background: rgba(99,102,241,0.08);
  padding: 0.4rem 0.8rem;
  border-radius: var(--radius-md);
}

.entry-notes {
  background: rgba(148,163,184,0.05);
  border-left: 3px solid var(--border-strong);
  padding: 0.875rem 1rem;
  font-size: 0.875rem;
  color: var(--text-default);
  margin: 0;
  border-radius: 0 var(--radius-md) var(--radius-md) 0;
  display: flex;
  gap: 0.5rem;
  align-items: flex-start;
}

/* ─── Lines Table ─────────────────────────────────────────── */
.je-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8125rem;
}

.je-table thead tr {
  background: var(--bg-app);
  border-bottom: 1px solid var(--border-default);
}

.je-table th {
  padding: 0.875rem 1rem;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}

.je-table td {
  padding: 1rem;
  border-bottom: 1px solid var(--border-default);
  vertical-align: middle;
}

.line-debit { border-left: 3px solid var(--finance-income); }
.line-credit { border-left: 3px solid var(--finance-expense); }

.account-info {
  display: flex;
  align-items: center;
  gap: 0.625rem;
}

.account-code {
  font-family: 'Courier New', monospace;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--primary);
  background: rgba(99,102,241,0.08);
  padding: 0.15rem 0.4rem;
  border-radius: 4px;
}

.account-name { font-weight: 500; }
.line-desc { color: var(--text-muted); }
.text-lg { font-size: 1.05rem; }

.lines-total-row td {
  padding: 1rem;
  background: var(--bg-app);
  border-top: 2px solid var(--border-strong);
  border-bottom: none;
}

/* ─── Meta Sidebar ────────────────────────────────────────── */
.meta-list {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.meta-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.meta-label {
  font-size: 0.8125rem;
  color: var(--text-muted);
}

.meta-val {
  font-size: 0.875rem;
  color: var(--text-heading);
}

.text-muted-sm { font-size: 0.75rem; color: var(--text-muted); }

.badge-ref {
  font-size: 0.75rem;
  background: var(--bg-app);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-sm);
  padding: 0.15rem 0.4rem;
  font-weight: 600;
}

.meta-separator {
  height: 1px;
  background: var(--border-default);
  margin: 0.25rem 0;
}

/* ─── Attachments ─────────────────────────────────────────── */
.attachments-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.attachment-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  background: var(--bg-app);
  transition: all var(--transition-fast);
}

.attachment-card:hover { border-color: var(--primary); }

.att-icon {
  width: 2.25rem;
  height: 2.25rem;
  background: rgba(99,102,241,0.08);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  color: var(--primary);
  flex-shrink: 0;
}

.att-info { flex: 1; min-width: 0; }

.att-name {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-heading);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin: 0 0 0.125rem 0;
}

.att-size { font-size: 0.7rem; color: var(--text-muted); margin: 0; }

.att-dl {
  width: 2rem;
  height: 2rem;
  border: none;
  background: transparent;
  color: var(--text-muted);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.att-dl:hover {
  background: rgba(99,102,241,0.1);
  color: var(--primary);
}

.no-attachments {
  text-align: center;
  padding: 2rem 1rem;
  color: var(--text-muted);
}
.no-attachments i {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  display: block;
}
.no-attachments p { margin: 0; font-size: 0.8125rem; }

@media print {
  .app-header, .app-sidebar, .detail-grid .side-col, .ti-btn {
    display: none !important;
  }
  .detail-grid { grid-template-columns: 1fr; }
  .box { box-shadow: none; border: 1px solid #ddd; }
}
</style>
