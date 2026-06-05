<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PageHeader from '@/components/ui/PageHeader.vue'
import StatusBadge from '@/components/finance/StatusBadge.vue'
import DateRangePicker from '@/components/ui/DateRangePicker.vue'
import { formatCurrency, formatDate } from '@/utils/formatters'
import { accountingService } from '@/services/accountingService'

const router = useRouter()

// ─── State ────────────────────────────────────────────────
const loading      = ref(true)
const searchQuery  = ref('')
const filterStatus = ref('all')
const dateRange    = ref({ start: null, end: null })
const showNewModal = ref(false)
const editingEntryId = ref(null)
const selectedEntry = ref(null)
const entries      = ref([])
const metrics      = ref([])
const accountOptions = ref([])

// ─── Journal Entry Form ────────────────────────────────────
const emptyLine = () => ({
  id:          Date.now() + Math.random(),
  accountId:   null,
  accountName: '',
  accountCode: '',
  description: '',
  debit:       '',
  credit:      ''
})

const form = reactive({
  date:        new Date().toISOString().split('T')[0],
  reference:   '',
  description: '',
  lines:       [emptyLine(), emptyLine()]
})

const formErrors = reactive({})

const fallbackAccounts = [
  { id: 1, code: '1010', name: 'Business Checking',  type: 'asset' },
  { id: 2, code: '1100', name: 'Accounts Receivable', type: 'asset' },
  { id: 3, code: '2000', name: 'Accounts Payable',    type: 'liability' },
  { id: 4, code: '2100', name: 'Payroll Liabilities', type: 'liability' },
  { id: 5, code: '4100', name: 'Service Revenue',     type: 'revenue' },
  { id: 6, code: '5100', name: 'Payroll Expense',     type: 'expense' },
  { id: 7, code: '5200', name: 'Rent Expense',        type: 'expense' },
  { id: 8, code: '5300', name: 'Software & SaaS',     type: 'expense' },
]

// ─── Fetch Data ────────────────────────────────────────────
onMounted(async () => {
  try {
    const [entriesRes, accountsRes] = await Promise.all([
      accountingService.journalEntries(),
      accountingService.accounts()
    ])
    metrics.value = entriesRes.metrics || []
    entries.value = (entriesRes.records || []).map(e => ({
      id: e.id,
      entryNo: `JE-${String(e.id).padStart(4, '0')}`,
      date: e.date,
      description: e.description,
      reference: e.reference,
      status: e.status.toLowerCase(),
      createdBy: '',
      totalDebit: 0,
      totalCredit: 0,
      lines_count: e.lines,
      lines: []
    }))
    const rawAccounts = accountsRes.records || []
    accountOptions.value = rawAccounts.length
      ? rawAccounts.map(a => ({ id: a.id, code: a.code, name: a.name, type: (a.type || '').toLowerCase() }))
      : fallbackAccounts
  } catch (e) {
    console.error('Failed to load journal entries:', e)
    accountOptions.value = fallbackAccounts
  } finally {
    loading.value = false
  }
})

// ─── Computed ──────────────────────────────────────────────
const filteredEntries = computed(() => {
  return entries.value.filter(e => {
    const matchSearch = !searchQuery.value ||
      e.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      e.entryNo.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      e.reference.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchStatus = filterStatus.value === 'all' ||
      e.status === filterStatus.value
    return matchSearch && matchStatus
  })
})

const metricSummary = computed(() => {
  const m = Object.fromEntries(metrics.value.map(m => [m.label, m.value]))
  return m
})

// Totals for new entry form
const formTotalDebit = computed(() =>
  form.lines.reduce((s, l) => s + (parseFloat(l.debit) || 0), 0)
)

const formTotalCredit = computed(() =>
  form.lines.reduce((s, l) => s + (parseFloat(l.credit) || 0), 0)
)

const formIsBalanced = computed(() =>
  Math.abs(formTotalDebit.value - formTotalCredit.value) < 0.001 &&
  formTotalDebit.value > 0
)

// ─── Accounts list for line selector ──────────────────────
const selectAccount = (line, account) => {
  line.accountId   = account.id
  line.accountCode = account.code
  line.accountName = account.name
}

// ─── Methods ───────────────────────────────────────────────
const addLine = () => {
  form.lines.push(emptyLine())
}

const removeLine = (index) => {
  if (form.lines.length > 2) {
    form.lines.splice(index, 1)
  }
}

const openNewModal = () => {
  editingEntryId.value = null
  form.date        = new Date().toISOString().split('T')[0]
  form.reference   = ''
  form.description = ''
  form.lines       = [emptyLine(), emptyLine()]
  Object.keys(formErrors).forEach(k => delete formErrors[k])
  showNewModal.value = true
}

const editEntry = (entry) => {
  editingEntryId.value = entry.id
  form.date = entry.date
  form.reference = entry.reference || ''
  form.description = entry.description
  form.lines = (entry.lines && entry.lines.length ? entry.lines : [{ debit: 0, credit: 0 }]).map(l => {
    const match = accountOptions.value.find(a => a.code === l.accountCode)
    return {
      id: Date.now() + Math.random(),
      accountId: match?.id || null,
      accountName: l.accountName || '',
      accountCode: l.accountCode || '',
      description: l.description || '',
      debit: l.debit || '',
      credit: l.credit || ''
    }
  })
  if (form.lines.length < 2) form.lines.push(emptyLine())
  Object.keys(formErrors).forEach(k => delete formErrors[k])
  showNewModal.value = true
}

const validateForm = () => {
  Object.keys(formErrors).forEach(k => delete formErrors[k])
  if (!form.date)        formErrors.date = 'Date is required'
  if (!form.description) formErrors.description = 'Description is required'
  const hasEmptyAccount = form.lines.some(l => !l.accountId)
  if (hasEmptyAccount)   formErrors.lines = 'All lines must have an account'
  if (!formIsBalanced.value)
    formErrors.balance = 'Total debits must equal total credits'
  return Object.keys(formErrors).length === 0
}

const saveEntry = async (status = 'draft') => {
  if (!validateForm()) return

  const payload = {
    date:        form.date,
    description: form.description,
    reference:   form.reference,
    status,
    lines:       form.lines.map(l => ({
      accountCode: l.accountCode,
      accountName: l.accountName,
      debit:       parseFloat(l.debit)  || 0,
      credit:      parseFloat(l.credit) || 0
    }))
  }

  try {
    if (editingEntryId.value) {
      await accountingService.updateJournalEntry(editingEntryId.value, payload)
      const idx = entries.value.findIndex(e => e.id === editingEntryId.value)
      if (idx !== -1) {
        entries.value[idx] = { ...entries.value[idx], ...payload, totalDebit: formTotalDebit.value, totalCredit: formTotalCredit.value }
      }
    } else {
      const res = await accountingService.createJournalEntry(payload)
      const created = res.record || res
      entries.value.unshift({
        id: created.id,
        entryNo: `JE-${String(created.id).padStart(4, '0')}`,
        ...created,
        totalDebit: formTotalDebit.value,
        totalCredit: formTotalCredit.value
      })
    }
  } catch (e) {
    console.error('Failed to save journal entry:', e)
    const msg = e?.response?.data?.message || e?.message || 'Failed to save. Check your connection.'
    const detail = e?.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(', ') : ''
    alert(msg + (detail ? '\n\n' + detail : ''))
  }

  editingEntryId.value = null
  showNewModal.value = false
}

const postEntry = async (entry) => {
  if (entry.status === 'pending' || entry.status === 'draft') {
    try {
      await accountingService.updateJournalEntry(entry.id, { status: 'posted' })
      entry.status = 'posted'
    } catch (e) {
      console.error('Failed to post journal entry:', e)
      const msg = e?.response?.data?.message || e?.message || 'Failed to post. Check your connection.'
      alert(msg)
    }
  }
}

const deleteEntry = async (entry) => {
  if (confirm(`Delete journal entry ${entry.entryNo}? This cannot be undone.`)) {
    try {
      await accountingService.deleteJournalEntry(entry.id)
      entries.value = entries.value.filter(e => e.id !== entry.id)
    } catch (e) {
      console.error('Failed to delete journal entry:', e)
      const msg = e?.response?.data?.message || e?.message || 'Failed to delete. Check your connection.'
      alert(msg)
    }
  }
}

const voidEntry = (entry) => {
  if (confirm(`Void journal entry ${entry.entryNo}? This cannot be undone.`)) {
    entry.status = 'voided'
  }
}

const viewEntry = (entry) => {
  router.push(`/accounting/journal-entries/${entry.id}`)
}

const statusTabs = [
  { value: 'all',     label: 'All Entries' },
  { value: 'posted',  label: 'Posted'      },
  { value: 'pending', label: 'Pending'     },
  { value: 'draft',   label: 'Draft'       },
  { value: 'voided',  label: 'Voided'      }
]

const expandedEntry = ref(null)
const toggleExpand = (id) => {
  expandedEntry.value = expandedEntry.value === id ? null : id
}
</script>

<template>
  <div>
    <!-- Header -->
    <PageHeader
      title="Journal Entries"
      subtitle="Record and manage all double-entry bookkeeping transactions"
    >
      <template #actions>
        <button class="ti-btn btn-outline" type="button">
          <i class="ri-download-line"></i> Export
        </button>
        <button class="ti-btn btn-gradient" @click="openNewModal">
          <i class="ri-add-line"></i> New Entry
        </button>
      </template>
    </PageHeader>

    <!-- Summary Strip -->
    <div class="summary-strip">
      <div class="strip-card">
        <div class="strip-icon icon-primary">
          <i class="ri-article-line"></i>
        </div>
        <div>
          <p class="strip-label">Journal Entries</p>
          <p class="strip-val">{{ metricSummary['Journal entries'] || '0' }}</p>
        </div>
      </div>
      <div class="strip-divider"></div>
      <div class="strip-card">
        <div class="strip-icon icon-success">
          <i class="ri-checkbox-circle-line"></i>
        </div>
        <div>
          <p class="strip-label">Posted</p>
          <p class="strip-val text-success">{{ metricSummary['Posted entries'] || '0' }}</p>
        </div>
      </div>
      <div class="strip-divider"></div>
      <div class="strip-card">
        <div class="strip-icon icon-muted">
          <i class="ri-draft-line"></i>
        </div>
        <div>
          <p class="strip-label">Drafts</p>
          <p class="strip-val">{{ metricSummary['Draft entries'] || '0' }}</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="box">
      <div class="box-body">
        <div class="filter-row">
          <!-- Search -->
          <div class="search-wrap">
            <i class="ri-search-line s-icon"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by description, entry no, or reference..."
              class="search-input"
            />
          </div>

          <!-- Date Range -->
          <DateRangePicker v-model="dateRange" label="" />

          <!-- Status Tabs -->
          <div class="status-tabs">
            <button
              v-for="tab in statusTabs"
              :key="tab.value"
              class="status-tab"
              :class="{ 'tab-active': filterStatus === tab.value }"
              @click="filterStatus = tab.value"
            >
              {{ tab.label }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Entries Table -->
    <div class="box">
      <div class="box-header justify-between">
        <div class="box-title">
          {{ filteredEntries.length }} Journal
          {{ filteredEntries.length === 1 ? 'Entry' : 'Entries' }}
        </div>
      </div>

      <div class="box-body p-0">
        <!-- Empty -->
        <div v-if="filteredEntries.length === 0" class="empty-state">
          <div class="empty-icon">
            <i class="ri-article-line"></i>
          </div>
          <p class="empty-title">No journal entries found</p>
          <p class="empty-sub">
            Try adjusting your search or create a new entry.
          </p>
          <button class="ti-btn btn-gradient mt-3" @click="openNewModal">
            <i class="ri-add-line"></i> New Entry
          </button>
        </div>

        <!-- Table -->
        <table v-else class="table-standard">
          <thead>
            <tr>
              <th style="width:40px"></th>
              <th>Entry No.</th>
              <th>Date</th>
              <th>Description</th>
              <th>Reference</th>
              <th>Status</th>
              <th>Created By</th>
              <th class="text-end">Debit</th>
              <th class="text-end">Credit</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <template
              v-for="entry in filteredEntries"
              :key="entry.id"
            >
              <!-- Main Row -->
              <tr
                class="je-row"
                :class="{ 'row-voided': entry.status === 'voided' }"
              >
                <!-- Expand Toggle -->
                <td>
                  <button
                    class="expand-btn"
                    @click="toggleExpand(entry.id)"
                  >
                    <i :class="expandedEntry === entry.id
                      ? 'ri-arrow-down-s-line'
                      : 'ri-arrow-right-s-line'"
                    ></i>
                  </button>
                </td>

                <!-- Entry No -->
                <td>
                  <span class="entry-no">{{ entry.entryNo }}</span>
                </td>

                <!-- Date -->
                <td class="text-muted-sm">
                  {{ formatDate(entry.date) }}
                </td>

                <!-- Description -->
                <td>
                  <p class="entry-desc">{{ entry.description }}</p>
                </td>

                <!-- Reference -->
                <td>
                  <span class="ref-badge">{{ entry.reference }}</span>
                </td>

                <!-- Status -->
                <td>
                  <StatusBadge :status="entry.status" size="sm" />
                </td>

                <!-- Created By -->
                <td class="text-muted-sm">
                  {{ entry.createdBy }}
                </td>

                <!-- Debit -->
                <td class="text-end tabular-nums font-medium text-success">
                  {{ formatCurrency(entry.totalDebit) }}
                </td>

                <!-- Credit -->
                <td class="text-end tabular-nums font-medium text-danger">
                  {{ formatCurrency(entry.totalCredit) }}
                </td>

                <!-- Actions -->
                <td class="text-center">
                  <div class="row-actions">
                    <button
                      class="act-btn btn-view"
                      title="View Detail"
                      @click="viewEntry(entry)"
                    >
                      <i class="ri-eye-line"></i>
                    </button>
                    <button
                      v-if="entry.status === 'pending' || entry.status === 'draft'"
                      class="act-btn btn-post"
                      title="Post Entry"
                      @click="postEntry(entry)"
                    >
                      <i class="ri-check-double-line"></i>
                    </button>
                    <button
                      v-if="entry.status === 'pending' || entry.status === 'draft'"
                      class="act-btn btn-edit"
                      title="Edit Entry"
                      @click="editEntry(entry)"
                    >
                      <i class="ri-pencil-line"></i>
                    </button>
                    <button
                      v-if="entry.status === 'pending' || entry.status === 'draft'"
                      class="act-btn btn-delete"
                      title="Delete Entry"
                      @click="deleteEntry(entry)"
                    >
                      <i class="ri-delete-bin-line"></i>
                    </button>
                    <button
                      v-if="entry.status !== 'voided'"
                      class="act-btn btn-void"
                      title="Void Entry"
                      @click="voidEntry(entry)"
                    >
                      <i class="ri-close-circle-line"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Expanded Lines -->
              <tr
                v-if="expandedEntry === entry.id"
                class="lines-row"
              >
                <td colspan="10" class="lines-td">
                  <div class="lines-wrap">
                    <p class="lines-heading">
                      <i class="ri-indent-increase"></i>
                      Transaction Lines
                    </p>
                    <table class="lines-table">
                      <thead>
                        <tr>
                          <th>Account Code</th>
                          <th>Account Name</th>
                          <th class="text-end">Debit (DR)</th>
                          <th class="text-end">Credit (CR)</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="(line, li) in entry.lines"
                          :key="li"
                          :class="line.debit > 0
                            ? 'line-debit'
                            : 'line-credit'"
                        >
                          <td>
                            <span class="line-code">
                              {{ line.accountCode }}
                            </span>
                          </td>
                          <td :class="line.credit > 0 ? 'pl-8' : ''">
                            {{ line.accountName }}
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
                          <td colspan="2" class="font-semibold">Total</td>
                          <td class="text-end tabular-nums text-success font-bold">
                            {{ formatCurrency(entry.totalDebit) }}
                          </td>
                          <td class="text-end tabular-nums text-danger font-bold">
                            {{ formatCurrency(entry.totalCredit) }}
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- New Journal Entry Modal -->
    <transition name="modal-fade">
      <div
        v-if="showNewModal"
        class="modal-backdrop"
        @click.self="showNewModal = false"
      >
        <div class="modal-box modal-lg">
          <!-- Header -->
          <div class="modal-header">
            <div class="modal-title-wrap">
              <div class="modal-icon">
                <i class="ri-article-line"></i>
              </div>
              <div>
                <h3 class="modal-title">{{ editingEntryId ? 'Edit Journal Entry' : 'New Journal Entry' }}</h3>
                <p class="modal-sub">
                  Debits must equal Credits before posting
                </p>
              </div>
            </div>
            <button class="modal-close" @click="showNewModal = false">
              <i class="ri-close-line"></i>
            </button>
          </div>

          <!-- Body -->
          <div class="modal-body">
            <!-- Meta Fields -->
            <div class="meta-grid">
              <div class="field-grp">
                <label class="field-lbl">
                  Date <span class="req">*</span>
                </label>
                <input
                  v-model="form.date"
                  type="date"
                  class="field-ctrl"
                  :class="{ 'ctrl-error': formErrors.date }"
                />
                <p v-if="formErrors.date" class="err">{{ formErrors.date }}</p>
              </div>

              <div class="field-grp">
                <label class="field-lbl">Reference No.</label>
                <input
                  v-model="form.reference"
                  type="text"
                  placeholder="e.g. INV-2045"
                  class="field-ctrl"
                />
              </div>

              <div class="field-grp field-full">
                <label class="field-lbl">
                  Description <span class="req">*</span>
                </label>
                <input
                  v-model="form.description"
                  type="text"
                  placeholder="Brief description of this transaction..."
                  class="field-ctrl"
                  :class="{ 'ctrl-error': formErrors.description }"
                />
                <p v-if="formErrors.description" class="err">
                  {{ formErrors.description }}
                </p>
              </div>
            </div>

            <!-- Balance Error -->
            <div
              v-if="formErrors.balance || formErrors.lines"
              class="balance-alert"
            >
              <i class="ri-error-warning-line"></i>
              {{ formErrors.balance || formErrors.lines }}
            </div>

            <!-- Lines Table -->
            <div class="lines-editor">
              <table class="editor-table">
                <thead>
                  <tr>
                    <th>Account</th>
                    <th>Description</th>
                    <th class="text-end">Debit (DR)</th>
                    <th class="text-end">Credit (CR)</th>
                    <th style="width:40px"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(line, idx) in form.lines"
                    :key="line.id"
                    class="editor-row"
                  >
                    <!-- Account Selector -->
                    <td>
                      <select
                        class="line-select"
                        :value="line.accountId"
                        @change="selectAccount(
                          line,
                          accountOptions.find(
                            a => a.id === parseInt($event.target.value)
                          )
                        )"
                      >
                        <option value="">Select account...</option>
                        <option
                          v-for="acc in accountOptions"
                          :key="acc.id"
                          :value="acc.id"
                        >
                          {{ acc.code }} — {{ acc.name }}
                        </option>
                      </select>
                    </td>

                    <!-- Line Description -->
                    <td>
                      <input
                        v-model="line.description"
                        type="text"
                        placeholder="Optional note..."
                        class="line-input"
                      />
                    </td>

                    <!-- Debit -->
                    <td>
                      <input
                        v-model="line.debit"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="0.00"
                        class="line-amount line-dr"
                        @focus="line.credit = ''"
                      />
                    </td>

                    <!-- Credit -->
                    <td>
                      <input
                        v-model="line.credit"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="0.00"
                        class="line-amount line-cr"
                        @focus="line.debit = ''"
                      />
                    </td>

                    <!-- Remove -->
                    <td>
                      <button
                        class="remove-line"
                        :disabled="form.lines.length <= 2"
                        @click="removeLine(idx)"
                      >
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="editor-totals">
                    <td colspan="2" class="totals-label">
                      <button class="add-line-btn" @click="addLine">
                        <i class="ri-add-circle-line"></i>
                        Add Line
                      </button>
                    </td>
                    <td class="text-end">
                      <p class="totals-heading">Total DR</p>
                      <p
                        class="totals-amount tabular-nums"
                        :class="formIsBalanced
                          ? 'text-success'
                          : 'text-warning'"
                      >
                        {{ formatCurrency(formTotalDebit) }}
                      </p>
                    </td>
                    <td class="text-end">
                      <p class="totals-heading">Total CR</p>
                      <p
                        class="totals-amount tabular-nums"
                        :class="formIsBalanced
                          ? 'text-success'
                          : 'text-warning'"
                      >
                        {{ formatCurrency(formTotalCredit) }}
                      </p>
                    </td>
                    <td></td>
                  </tr>
                  <!-- Balance Indicator -->
                  <tr>
                    <td colspan="5" class="balance-row">
                      <div
                        class="balance-indicator"
                        :class="formIsBalanced
                          ? 'balanced'
                          : 'unbalanced'"
                      >
                        <i :class="formIsBalanced
                          ? 'ri-shield-check-line'
                          : 'ri-shield-cross-line'"
                        ></i>
                        <span v-if="formIsBalanced">
                          Entry is balanced — ready to post
                        </span>
                        <span v-else>
                          Difference:
                          {{ formatCurrency(
                            Math.abs(formTotalDebit - formTotalCredit)
                          ) }}
                          — must equal zero
                        </span>
                      </div>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer">
            <button
              class="ti-btn btn-ghost"
              @click="showNewModal = false"
            >
              Cancel
            </button>
            <button
              class="ti-btn btn-outline"
              @click="saveEntry('draft')"
            >
              <i class="ri-save-line"></i>
              Save Draft
            </button>
            <button
              class="ti-btn btn-gradient"
              :disabled="!formIsBalanced"
              @click="saveEntry('pending')"
            >
              <i class="ri-send-plane-line"></i>
              Submit for Approval
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
/* ─── Summary Strip ───────────────────────────────────────── */
.summary-strip {
  display: flex;
  align-items: center;
  background: var(--bg-card);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  padding: 1.25rem 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: var(--shadow-card);
  gap: 1rem;
  flex-wrap: wrap;
}

.strip-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
  min-width: 140px;
}

.strip-icon {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}

.icon-primary { background: rgba(99,102,241,0.1);  color: var(--primary);         }
.icon-success { background: rgba(16,185,129,0.1);  color: var(--finance-income);  }
.icon-warning { background: rgba(245,158,11,0.1);  color: var(--finance-pending); }
.icon-muted   { background: rgba(148,163,184,0.1); color: var(--text-muted);      }

.strip-label {
  font-size: 0.7rem;
  color: var(--text-muted);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.strip-val {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text-heading);
}

.strip-divider {
  width: 1px;
  height: 36px;
  background: var(--border-default);
  flex-shrink: 0;
}

/* ─── Filter Row ──────────────────────────────────────────── */
.filter-row {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  flex-wrap: wrap;
}

.search-wrap {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: 1.5px solid var(--border-default);
  border-radius: var(--radius-md);
  padding: 0 0.75rem;
  height: 2.5rem;
  flex: 1;
  min-width: 220px;
  transition: border-color var(--transition-fast);
}

.search-wrap:focus-within {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

.s-icon { color: var(--text-muted); font-size: 0.875rem; }

.search-input {
  flex: 1;
  border: none;
  outline: none;
  background: none;
  font-size: 0.8125rem;
  color: var(--text-default);
}

.status-tabs {
  display: flex;
  gap: 0.25rem;
  flex-wrap: wrap;
}

.status-tab {
  padding: 0.375rem 0.875rem;
  border: 1.5px solid var(--border-default);
  border-radius: 99px;
  background: none;
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--text-muted);
  cursor: pointer;
  transition: all var(--transition-fast);
  white-space: nowrap;
}

.status-tab:hover { border-color: var(--primary); color: var(--primary); }
.tab-active {
  background: var(--primary);
  border-color: var(--primary);
  color: white;
}

/* ─── Journal Entry Table ─────────────────────────────────── */
.je-row {
  transition: background var(--transition-fast);
  cursor: pointer;
}

.row-voided td {
  opacity: 0.5;
  text-decoration: line-through;
}

/* ─── Entry Cells ─────────────────────────────────────────── */
.expand-btn {
  width: 1.5rem;
  height: 1.5rem;
  border: none;
  background: var(--bg-app);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--text-muted);
  font-size: 1rem;
  transition: all var(--transition-fast);
}

.expand-btn:hover {
  background: rgba(99,102,241,0.1);
  color: var(--primary);
}

.entry-no {
  font-family: 'Courier New', monospace;
  font-size: 0.8125rem;
  font-weight: 700;
  color: var(--primary);
  background: rgba(99,102,241,0.08);
  padding: 0.2rem 0.5rem;
  border-radius: var(--radius-sm);
}

.entry-desc {
  font-weight: 500;
  color: var(--text-heading);
  max-width: 260px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ref-badge {
  font-size: 0.7rem;
  background: var(--bg-app);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-sm);
  padding: 0.2rem 0.5rem;
  color: var(--text-muted);
  font-weight: 500;
}

.text-muted-sm {
  font-size: 0.8rem;
  color: var(--text-muted);
}

/* ─── Row Actions ─────────────────────────────────────────── */
.row-actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.375rem;
}

.act-btn {
  width: 1.875rem;
  height: 1.875rem;
  border: none;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.btn-view  { background: rgba(99,102,241,0.1);  color: var(--primary);         }
.btn-post  { background: rgba(16,185,129,0.1);  color: var(--finance-income);  }
.btn-edit  { background: rgba(251,191,36,0.15); color: rgb(217, 160, 18);      }
.btn-delete{ background: rgba(244,63,94,0.1);   color: var(--finance-expense); }
.btn-void   { background: rgba(148,163,184,0.15); color: var(--text-muted);    }
.act-btn:hover { filter: brightness(1.15); transform: scale(1.08); }

/* ─── Expanded Lines ──────────────────────────────────────── */
.lines-row td { padding: 0; border-bottom: 1px solid var(--border-default); }

.lines-td { padding: 0 !important; }

.lines-wrap {
  background: rgba(99,102,241,0.02);
  border-top: 1px dashed var(--border-default);
  padding: 1rem 1.25rem;
}

.lines-heading {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.lines-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8rem;
}

.lines-table th {
  padding: 0.5rem 0.75rem;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  border-bottom: 1px solid var(--border-default);
}

.lines-table td {
  padding: 0.5rem 0.75rem;
  border-bottom: 1px solid var(--border-default);
}

.lines-table tbody tr:last-child td { border-bottom: none; }

.line-debit  { border-left: 3px solid var(--finance-income);  }
.line-credit { border-left: 3px solid var(--finance-expense); }

.line-code {
  font-family: 'Courier New', monospace;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--primary);
  background: rgba(99,102,241,0.08);
  padding: 0.15rem 0.4rem;
  border-radius: 4px;
}

.pl-8 { padding-left: 2rem; }

.lines-total-row td {
  padding: 0.625rem 0.75rem;
  background: var(--bg-app);
  border-top: 2px solid var(--border-strong);
}

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

/* ─── Modal ───────────────────────────────────────────────── */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15,23,42,0.6);
  backdrop-filter: blur(4px);
  z-index: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-box {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  width: 100%;
  max-width: 520px;
  box-shadow: 0 25px 50px rgba(0,0,0,0.25);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  overflow: hidden;
}

.modal-lg { max-width: 780px; }

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-default);
  flex-shrink: 0;
}

.modal-title-wrap {
  display: flex;
  align-items: flex-start;
  gap: 0.875rem;
}

.modal-icon {
  width: 2.5rem;
  height: 2.5rem;
  background: rgba(99,102,241,0.1);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  color: var(--primary);
  flex-shrink: 0;
}

.modal-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-heading);
}

.modal-sub {
  font-size: 0.8rem;
  color: var(--text-muted);
  margin-top: 0.2rem;
}

.modal-close {
  width: 2rem;
  height: 2rem;
  border: none;
  background: var(--bg-app);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: var(--text-muted);
  cursor: pointer;
  transition: all var(--transition-fast);
  flex-shrink: 0;
}

.modal-close:hover {
  background: rgba(244,63,94,0.1);
  color: var(--finance-expense);
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.25rem 1.5rem;
  border-top: 1px solid var(--border-default);
  background: var(--bg-app);
  flex-shrink: 0;
}

/* ─── Meta Grid ───────────────────────────────────────────── */
.meta-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.field-full { grid-column: 1 / -1; }

.field-grp {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.field-lbl {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text-default);
}

.req { color: var(--finance-expense); }

.field-ctrl {
  height: 2.5rem;
  padding: 0 0.875rem;
  border: 1.5px solid var(--border-default);
  border-radius: var(--radius-md);
  background: var(--bg-card);
  font-size: 0.8125rem;
  color: var(--text-default);
  outline: none;
  transition: all var(--transition-fast);
  font-family: inherit;
}

.field-ctrl:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}

.ctrl-error {
  border-color: var(--finance-expense) !important;
}

.err {
  font-size: 0.75rem;
  color: var(--finance-expense);
}

/* ─── Balance Alert ───────────────────────────────────────── */
.balance-alert {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(244,63,94,0.08);
  border: 1px solid rgba(244,63,94,0.25);
  border-left: 3px solid var(--finance-expense);
  border-radius: var(--radius-md);
  padding: 0.75rem 1rem;
  font-size: 0.8125rem;
  color: var(--finance-expense);
}

/* ─── Lines Editor ────────────────────────────────────────── */
.lines-editor {
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.editor-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8125rem;
}

.editor-table thead tr {
  background: var(--bg-app);
  border-bottom: 1px solid var(--border-default);
}

.editor-table th {
  padding: 0.625rem 0.75rem;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}

.editor-row td {
  padding: 0.5rem 0.5rem;
  border-bottom: 1px solid var(--border-default);
  vertical-align: middle;
}

.line-select {
  width: 100%;
  height: 2.25rem;
  padding: 0 0.5rem;
  border: 1.5px solid var(--border-default);
  border-radius: var(--radius-md);
  background: var(--bg-card);
  font-size: 0.8rem;
  color: var(--text-default);
  outline: none;
  transition: border-color var(--transition-fast);
}

.line-select:focus { border-color: var(--primary); }

.line-input {
  width: 100%;
  height: 2.25rem;
  padding: 0 0.625rem;
  border: 1.5px solid var(--border-default);
  border-radius: var(--radius-md);
  background: var(--bg-card);
  font-size: 0.8rem;
  color: var(--text-default);
  outline: none;
  transition: border-color var(--transition-fast);
  font-family: inherit;
}

.line-input:focus { border-color: var(--primary); }

.line-amount {
  width: 100%;
  height: 2.25rem;
  padding: 0 0.625rem;
  border: 1.5px solid var(--border-default);
  border-radius: var(--radius-md);
  font-size: 0.8rem;
  font-variant-numeric: tabular-nums;
  font-feature-settings: "tnum" 1;
  text-align: right;
  outline: none;
  transition: border-color var(--transition-fast);
  font-family: inherit;
}

.line-amount:focus { outline: none; }

.line-dr {
  background: rgba(16,185,129,0.04);
  border-color: rgba(16,185,129,0.3);
  color: var(--finance-income);
}

.line-dr:focus {
  border-color: var(--finance-income);
  box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
}

.line-cr {
  background: rgba(244,63,94,0.04);
  border-color: rgba(244,63,94,0.3);
  color: var(--finance-expense);
}

.line-cr:focus {
  border-color: var(--finance-expense);
  box-shadow: 0 0 0 3px rgba(244,63,94,0.1);
}

.remove-line {
  width: 1.75rem;
  height: 1.75rem;
  border: none;
  background: rgba(244,63,94,0.1);
  border-radius: var(--radius-sm);
  color: var(--finance-expense);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  transition: all var(--transition-fast);
}

.remove-line:hover:not(:disabled) {
  background: var(--finance-expense);
  color: white;
}

.remove-line:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

/* ─── Editor Totals ───────────────────────────────────────── */
.editor-totals td {
  padding: 0.75rem;
  background: var(--bg-app);
  border-top: 2px solid var(--border-strong);
}

.totals-label {
  vertical-align: middle;
}

.add-line-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  background: none;
  border: 1.5px dashed var(--primary);
  border-radius: var(--radius-md);
  padding: 0.375rem 0.75rem;
  font-size: 0.8rem;
  color: var(--primary);
  cursor: pointer;
  font-weight: 500;
  transition: all var(--transition-fast);
}

.add-line-btn:hover {
  background: rgba(99,102,241,0.06);
}

.totals-heading {
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  margin-bottom: 0.2rem;
}

.totals-amount {
  font-size: 1rem;
  font-weight: 700;
}

/* ─── Balance Indicator ───────────────────────────────────── */
.balance-row td {
  padding: 0;
  border-top: 1px solid var(--border-default);
}

.balance-indicator {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.625rem 0.75rem;
  font-size: 0.8rem;
  font-weight: 600;
}

.balanced {
  background: rgba(16,185,129,0.08);
  color: var(--finance-income);
  border-top: 1px solid rgba(16,185,129,0.2);
}

.unbalanced {
  background: rgba(244,63,94,0.06);
  color: var(--finance-expense);
}

/* ─── Buttons ─────────────────────────────────────────────── */
.btn-gradient {
  background: linear-gradient(
    135deg, var(--primary), var(--primarytint1color)
  );
  color: white;
  border: none;
  box-shadow: 0 4px 10px rgba(92, 103, 247, 0.25);
}

.btn-gradient:hover {
  filter: brightness(1.1);
}

.btn-outline {
  background: transparent;
  color: var(--primary);
  border: 1.5px solid var(--primary);
}

.btn-outline:hover {
  background: rgba(92, 103, 247, 0.08);
}

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

.ti-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Modal animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
