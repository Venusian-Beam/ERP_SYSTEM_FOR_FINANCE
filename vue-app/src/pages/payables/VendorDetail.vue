<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import PageHeader from '@/components/ui/PageHeader.vue'

const router = useRouter()
const route = useRoute()

// Mock data based on ID
const vendorId = route.params.id

const loading = ref(true)
const vendor = ref(null)
const recentBills = ref([])

onMounted(async () => {
  try {
    const data = await payablesService.vendor(route.params.id)
    const v = data.record || data
    vendor.value = {
      id: v.id,
      name: v.name,
      email: v.email || '—',
      phone: v.phone || '—',
      address: v.address || '—',
      category: v.category || '—',
      taxId: v.tax_id || '—',
      paymentTerms: v.payment_terms || 'Net 30',
      openBalance: Number(v.open_balance) || 0,
      totalPaid: Number(v.total_paid) || 0,
      status: v.status || 'Active'
    }
  } catch (e) {
    console.error('Failed to load vendor:', e)
  } finally {
    loading.value = false
  }
})

const goBack = () => {
  router.push('/payables/vendors')
}

import { payablesService } from '@/services/payablesService'
</script>

<template>
  <div>
    <PageHeader :title="vendor.name" subtitle="Vendor Profile & Overview">
      <template #actions>
        <button class="ti-btn btn-outline" @click="goBack">
          <i class="ri-arrow-left-line"></i> Back to Vendors
        </button>
        <button class="ti-btn btn-primary-soft">
          <i class="ri-pencil-line"></i> Edit Vendor
        </button>
        <button class="ti-btn btn-gradient">
          <i class="ri-file-add-line"></i> Create Bill
        </button>
      </template>
    </PageHeader>

    <div class="row">
      <!-- Left Column: Vendor Info -->
      <div class="col-lg-4">
        <div class="box">
          <div class="box-body">
            <div class="vendor-header">
              <div class="vendor-avatar">
                {{ vendor.name.charAt(0) }}
              </div>
              <div>
                <h4 class="vendor-name">{{ vendor.name }}</h4>
                <span class="badge" :class="vendor.status === 'Active' ? 'bg-success-transparent' : 'bg-light'">
                  {{ vendor.status }}
                </span>
              </div>
            </div>

            <div class="info-group">
              <label>Contact Info</label>
              <div class="info-item">
                <i class="ri-mail-line"></i> {{ vendor.email }}
              </div>
              <div class="info-item">
                <i class="ri-phone-line"></i> {{ vendor.phone }}
              </div>
              <div class="info-item align-top">
                <i class="ri-map-pin-line"></i> 
                <span>{{ vendor.address }}</span>
              </div>
            </div>

            <hr class="divider" />

            <div class="info-group">
              <label>Business Details</label>
              <div class="info-item">
                <span class="info-label">Category:</span> {{ vendor.category }}
              </div>
              <div class="info-item">
                <span class="info-label">Tax ID:</span> {{ vendor.taxId }}
              </div>
              <div class="info-item">
                <span class="info-label">Terms:</span> {{ vendor.paymentTerms }}
              </div>
            </div>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="box metric-card bg-primary-gradient">
          <div class="box-body">
            <div class="metric-title">Open Balance</div>
            <div class="metric-value">GHC {{ vendor.openBalance.toLocaleString() }}</div>
            <div class="metric-subtitle">Currently outstanding</div>
          </div>
        </div>
      </div>

      <!-- Right Column: Recent Activity -->
      <div class="col-lg-8">
        <div class="box">
          <div class="box-header">
            <h5 class="box-title">Recent Bills</h5>
            <button class="btn btn-sm btn-outline-primary">View All</button>
          </div>
          <div class="box-body p-0">
            <div class="table-responsive">
              <table class="ti-custom-table mb-0 table-standard">
                <thead>
                  <tr>
                    <th>Bill #</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="bill in recentBills" :key="bill.id">
                    <td class="font-medium text-primary">{{ bill.id }}</td>
                    <td>{{ bill.date }}</td>
                    <td>{{ bill.dueDate }}</td>
                    <td class="text-end font-semibold">GHC {{ bill.amount.toLocaleString() }}</td>
                    <td>
                      <span class="badge" :class="bill.status === 'Open' ? 'bg-warning-transparent' : 'bg-success-transparent'">
                        {{ bill.status }}
                      </span>
                    </td>
                    <td class="text-end">
                      <button class="btn btn-sm btn-icon btn-light">
                        <i class="ri-arrow-right-s-line"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</template>

<style scoped>
.row {
  display: flex;
  flex-wrap: wrap;
  margin: -1rem;
}

.col-lg-4 {
  width: 33.333%;
  padding: 1rem;
}

.col-lg-8 {
  width: 66.666%;
  padding: 1rem;
}

@media (max-width: 991px) {
  .col-lg-4, .col-lg-8 { width: 100%; }
}

.vendor-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.vendor-avatar {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  background: rgba(99,102,241,0.1);
  color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 700;
}

.vendor-name {
  margin: 0 0 0.25rem 0;
  font-size: 1.25rem;
  color: var(--text-heading);
}

.info-group {
  margin-bottom: 1.5rem;
}

.info-group label {
  display: block;
  font-size: 0.75rem;
  text-transform: uppercase;
  color: var(--text-muted);
  font-weight: 700;
  margin-bottom: 0.75rem;
  letter-spacing: 0.05em;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
  font-size: 0.875rem;
  color: var(--text-default);
}

.info-item i {
  color: var(--text-muted);
  font-size: 1.1rem;
}

.align-top {
  align-items: flex-start;
}
.align-top i {
  margin-top: 0.1rem;
}

.info-label {
  color: var(--text-muted);
  width: 70px;
}

.divider {
  border: none;
  border-top: 1px solid var(--border-default);
  margin: 1.5rem 0;
}

.metric-card {
  color: white;
  border: none;
}

.bg-primary-gradient {
  background: linear-gradient(135deg, var(--primary), var(--primarytint1color));
}

.metric-title {
  font-size: 0.875rem;
  opacity: 0.8;
  margin-bottom: 0.5rem;
}

.metric-value {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.metric-subtitle {
  font-size: 0.75rem;
  opacity: 0.7;
}

.box-header {
  padding: 1.25rem;
  border-bottom: 1px solid var(--border-default);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.box-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
}

.p-0 { padding: 0 !important; }
.mb-0 { margin-bottom: 0 !important; }

.ti-custom-table {
  width: 100%;
  border-collapse: collapse;
}

.ti-custom-table th {
  padding: 0.75rem 1.25rem;
  font-size: 0.75rem;
  text-transform: uppercase;
  color: var(--text-muted);
  border-bottom: 1px solid var(--border-strong);
  background: var(--bg-app);
}

.ti-custom-table td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border-default);
  vertical-align: middle;
  font-size: 0.875rem;
}

/* Utilities */
.btn-outline-primary {
  border: 1px solid var(--primary);
  color: var(--primary);
  background: transparent;
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
  border-radius: var(--radius-sm);
  cursor: pointer;
}

.btn-icon {
  width: 28px;
  height: 28px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-sm);
  border: none;
  cursor: pointer;
}

.btn-light { background: var(--bg-app); border: 1px solid var(--border-default); }
.btn-light:hover { background: var(--border-default); }

.badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
}

.bg-success-transparent { background: rgba(34,197,94,0.1); color: #22c55e; }
.bg-warning-transparent { background: rgba(245,158,11,0.1); color: #f59e0b; }
.bg-light { background: #f1f5f9; color: #475569; }

.ti-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: var(--radius-md);
  font-weight: 600;
  cursor: pointer;
}

.btn-gradient {
  background: linear-gradient(135deg, var(--primary), var(--primarytint1color));
  color: white;
  border: none;
  box-shadow: 0 4px 10px rgba(92, 103, 247, 0.25);
}
.btn-gradient:hover { filter: brightness(1.1); }

.btn-primary-soft {
  background: rgba(92, 103, 247, 0.1);
  color: var(--primary);
  border: none;
}
.btn-primary-soft:hover { background: var(--primary); color: white; }

.btn-outline {
  background: transparent;
  color: var(--text-default);
  border: 1px solid var(--border-default);
}
.btn-outline:hover { background: var(--bg-app); }

.text-primary { color: var(--primary); }
</style>
