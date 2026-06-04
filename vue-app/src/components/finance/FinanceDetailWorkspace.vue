<script setup>
import PageHeader from '@/components/ui/PageHeader.vue'

const props = defineProps({
  title: String,
  subtitle: String,
  status: { type: String, default: 'Open' },
  amountLabel: { type: String, default: 'Balance Due' },
  amount: String,
  details: { type: Array, default: () => [] },
  lines: { type: Array, default: () => [] },
  timeline: { type: Array, default: () => [] },
  primaryAction: { type: String, default: 'Primary Action' },
})
</script>

<template>
  <section class="detail-workspace">
    <PageHeader :title="title" :subtitle="subtitle">
      <template #actions>
        <button class="secondary"><i class="ri-download-2-line"></i> Download PDF</button>
        <button class="primary"><i class="ri-check-line"></i> {{ primaryAction }}</button>
      </template>
    </PageHeader>
    <div class="detail-grid">
      <main>
        <article class="hero-card">
          <div><span class="eyebrow">{{ amountLabel }}</span><strong>{{ amount }}</strong><span class="status">{{ status }}</span></div>
          <div class="summary-grid">
            <div v-for="item in details" :key="item.label"><span>{{ item.label }}</span><b>{{ item.value }}</b></div>
          </div>
        </article>
        <article class="card">
          <header><div><h3>Line Items</h3><p>Financial coding and transaction breakdown</p></div><button><i class="ri-pencil-line"></i> Edit</button></header>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Description</th><th>Account</th><th class="right">Qty</th><th class="right">Rate</th><th class="right">Amount</th></tr></thead>
              <tbody><tr v-for="line in lines" :key="line.description"><td><b>{{ line.description }}</b><small>{{ line.note }}</small></td><td>{{ line.account }}</td><td class="right">{{ line.qty }}</td><td class="right">{{ line.rate }}</td><td class="right amount">{{ line.amount }}</td></tr></tbody>
            </table>
          </div>
          <div class="totals"><div><span>Subtotal</span><b>{{ amount }}</b></div><div><span>Tax</span><b>GHC 0.00</b></div><div class="grand"><span>Total</span><b>{{ amount }}</b></div></div>
        </article>
      </main>
      <aside>
        <article class="card">
          <header><div><h3>Activity Timeline</h3><p>Audit-ready transaction history</p></div></header>
          <ul class="timeline"><li v-for="event in timeline" :key="event.title"><i :class="event.icon || 'ri-check-line'"></i><div><b>{{ event.title }}</b><p>{{ event.text }}</p><span>{{ event.time }}</span></div></li></ul>
        </article>
        <article class="card attachment"><i class="ri-attachment-2"></i><div><b>Supporting documents</b><p>Invoice, receipt, and approval files are securely attached.</p></div><button>View files</button></article>
      </aside>
    </div>
  </section>
</template>

<style scoped>
.detail-workspace{display:flex;flex-direction:column;gap:1rem;width:100%;max-width:1180px;margin:0 auto}.detail-grid{display:grid;grid-template-columns:minmax(0,1.7fr) 300px;gap:.85rem}.detail-grid main,.detail-grid aside{display:flex;flex-direction:column;gap:.85rem}.hero-card,.card{background:var(--bg-card,#fff);border:1px solid var(--border-default,#e2e8f0);border-radius:.5rem;box-shadow:var(--shadow-card,0 1px 3px rgba(15,23,42,.06))}.hero-card{padding:1rem;background:var(--bg-card,#fff);border-left:3px solid #6366f1}.hero-card>div:first-child{display:flex;align-items:center;gap:.65rem}.eyebrow{font-size:.66rem;text-transform:uppercase;color:#64748b}.hero-card strong{font-size:1.35rem;color:#0f172a}.status{padding:.16rem .45rem;border-radius:.25rem;background:rgba(245,158,11,.12);color:#d97706;font-size:.64rem;font-weight:600}.summary-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.8rem;margin-top:.9rem;padding-top:.8rem;border-top:1px solid var(--border-default,#e2e8f0)}.summary-grid span,.summary-grid b{display:block}.summary-grid span{font-size:.65rem;color:#94a3b8}.summary-grid b{font-size:.74rem;color:var(--text-heading,#0f172a)}header{display:flex;justify-content:space-between;align-items:center;padding:.8rem .9rem;border-bottom:1px solid var(--border-default,#e2e8f0)}header h3{font-size:.82rem;margin:0}header p{font-size:.66rem;color:#94a3b8;margin:0}button{border-radius:.35rem;padding:.43rem .65rem;font-size:.7rem;font-weight:600;cursor:pointer}.primary{border:1px solid #6366f1;color:#fff;background:#6366f1}.secondary,header button{border:1px solid var(--border-default,#e2e8f0);color:#475569;background:#fff}.table-wrap{overflow:auto}th,td{padding:.65rem .85rem;text-align:left;border-bottom:1px solid var(--border-default,#e2e8f0);font-size:.7rem}th{font-size:.62rem;text-transform:uppercase;color:#64748b;background:#f8fafc}.right{text-align:right}td b,td small{display:block}td small{color:#94a3b8}.amount{font-weight:700;color:#0f172a}.totals{margin-left:auto;width:220px;padding:.8rem .9rem}.totals div{display:flex;justify-content:space-between;padding:.3rem 0;font-size:.7rem}.totals .grand{padding-top:.55rem;margin-top:.3rem;border-top:1px solid #e2e8f0;font-size:.78rem}.timeline{list-style:none;padding:.85rem}.timeline li{display:flex;gap:.6rem;padding-bottom:.85rem}.timeline i{width:1.65rem;height:1.65rem;border-radius:.35rem;display:grid;place-items:center;background:rgba(99,102,241,.1);color:#6366f1;font-size:.8rem}.timeline b{font-size:.7rem}.timeline p,.timeline span{font-size:.63rem;color:#94a3b8;margin:0}.attachment{display:flex;gap:.6rem;align-items:center;padding:.85rem}.attachment>i{font-size:1rem;color:#6366f1}.attachment b{font-size:.7rem}.attachment p{font-size:.63rem;color:#94a3b8;margin:0}.attachment button{margin-left:auto;border:1px solid var(--border-default,#e2e8f0);color:#475569;background:#fff}@media(max-width:900px){.detail-grid{grid-template-columns:1fr}.summary-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:540px){.summary-grid{grid-template-columns:1fr}.hero-card>div:first-child{align-items:flex-start;flex-direction:column}}
.hero-card,.card{border-color:rgba(var(--primary-rgb),.12)}.hero-card{border-left-color:var(--primary)}.primary{border-color:var(--primary);background:var(--primary)}.primary:hover{background:var(--primarytint1color);border-color:var(--primarytint1color)}.timeline i,.attachment>i{color:var(--primary)}.timeline i{background:rgba(var(--primary-rgb),.1)}.secondary:hover,header button:hover,.attachment button:hover{color:var(--primary);border-color:rgba(var(--primary-rgb),.3);background:rgba(var(--primary-rgb),.04)}
</style>
