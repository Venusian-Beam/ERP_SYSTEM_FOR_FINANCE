<script setup>
import PageHeader from '@/components/ui/PageHeader.vue'
import { exportToCSV, exportToPDF } from '@/utils/exportUtils'

const props = defineProps({
  title: String,
  subtitle: String,
  period: { type: String, default: 'Year to date · January 1 - June 3, 2026' },
  metrics: { type: Array, default: () => [] },
  sections: { type: Array, default: () => [] },
  insight: Object,
})

const handleExportCSV = () => {
  const flatData = []
  props.sections.forEach(sec => {
    flatData.push({ category: `--- ${sec.title} ---`, amount: '' })
    sec.rows.forEach(row => {
      flatData.push({ category: row.label, amount: row.value })
    })
    flatData.push({ category: `Total ${sec.title}`, amount: sec.total })
    flatData.push({ category: '', amount: '' })
  })

  exportToCSV(flatData, [
    { label: 'Category', key: 'category' },
    { label: 'Amount', key: 'amount' }
  ], `${props.title.replace(/\s+/g, '_')}.csv`)
}

const handleExportPDF = () => {
  const flatData = []
  props.sections.forEach(sec => {
    flatData.push({ category: `--- ${sec.title} ---`, amount: '' })
    sec.rows.forEach(row => {
      flatData.push({ category: row.label, amount: row.value })
    })
    flatData.push({ category: `Total ${sec.title}`, amount: sec.total })
  })

  exportToPDF(flatData, [
    { label: 'Category', key: 'category' },
    { label: 'Amount', key: 'amount' }
  ], props.title, `${props.title.replace(/\s+/g, '_')}.pdf`)
}
</script>

<template>
  <section class="report-workspace">
    <PageHeader :title="title" :subtitle="subtitle">
      <template #actions>
        <button class="soft" @click="handleExportCSV"><i class="ri-file-excel-2-line"></i> Excel</button>
        <button class="primary" @click="handleExportPDF"><i class="ri-file-pdf-2-line"></i> Export PDF</button>
      </template>
    </PageHeader>
    <div class="report-controls"><div><i class="ri-calendar-line"></i><span>{{ period }}</span></div><div><button>YTD</button><button>Quarter</button><button>Month</button></div></div>
    <div class="metric-grid"><article v-for="metric in metrics" :key="metric.label"><span>{{ metric.label }}</span><strong>{{ metric.value }}</strong><small>{{ metric.note }}</small></article></div>
    <article v-if="insight" class="insight"><i class="ri-information-line"></i><div><b>{{ insight.title }}</b><p>{{ insight.text }}</p></div></article>
    <article class="statement">
      <header><div><h2>{{ title }}</h2><p>{{ period }}</p></div><span>USD</span></header>
      <section v-for="section in sections" :key="section.title">
        <h3>{{ section.title }}</h3>
        <div v-for="row in section.rows" :key="row.label" class="statement-row"><button>{{ row.label }} <i class="ri-arrow-right-s-line"></i></button><span>{{ row.value }}</span></div>
        <div class="section-total"><b>{{ section.totalLabel || `Total ${section.title}` }}</b><b>{{ section.total }}</b></div>
      </section>
    </article>
  </section>
</template>

<style scoped>
.report-workspace{display:flex;flex-direction:column;gap:1rem;width:100%;max-width:1080px;margin:0 auto}.report-controls,.statement,.metric-grid article,.insight{background:var(--bg-card,#fff);border:1px solid var(--border-default,#e2e8f0);border-radius:.5rem;box-shadow:var(--shadow-card,0 1px 3px rgba(15,23,42,.06))}.report-controls{display:flex;justify-content:space-between;align-items:center;max-width:720px;padding:.65rem .8rem;font-size:.7rem;color:#64748b}.report-controls div{display:flex;align-items:center;gap:.4rem}.report-controls button,.soft,.primary{border-radius:.35rem;padding:.4rem .62rem;font-size:.68rem;font-weight:600;cursor:pointer}.report-controls button,.soft{border:1px solid var(--border-default,#e2e8f0);background:#fff;color:#475569}.primary{border:1px solid #6366f1;background:#6366f1;color:white}.metric-grid{display:grid;grid-template-columns:repeat(3,minmax(180px,240px));gap:.8rem}.metric-grid article{padding:.8rem .85rem}.metric-grid span,.metric-grid strong,.metric-grid small{display:block}.metric-grid span{font-size:.64rem;color:#94a3b8;text-transform:uppercase}.metric-grid strong{font-size:1.08rem;color:#0f172a}.metric-grid small{font-size:.63rem;color:#10b981}.insight{display:flex;gap:.6rem;max-width:720px;padding:.7rem .8rem;background:var(--bg-card,#fff)}.insight i{color:#6366f1}.insight b{font-size:.72rem}.insight p{font-size:.66rem;color:#64748b;margin:0}.statement{max-width:860px;width:100%;overflow:hidden}.statement header{display:flex;justify-content:space-between;padding:.9rem 1rem;border-bottom:1px solid #e2e8f0}.statement header h2{font-size:.9rem}.statement header p,.statement header span{font-size:.64rem;color:#94a3b8}.statement section{padding:.8rem 1rem}.statement h3{font-size:.68rem;text-transform:uppercase;letter-spacing:.04em;color:#6366f1;margin-bottom:.3rem}.statement-row,.section-total{display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px dashed #e2e8f0;font-size:.7rem}.statement-row button{border:0;background:transparent;color:#475569;cursor:pointer;padding:0}.statement-row button:hover{color:#6366f1}.section-total{border-bottom:0;border-top:1px solid #cbd5e1;margin-top:.2rem;color:#0f172a}@media(max-width:700px){.metric-grid{grid-template-columns:1fr}.report-controls{align-items:flex-start;flex-direction:column;gap:.6rem}}
.report-controls,.statement,.metric-grid article,.insight{border-color:rgba(var(--primary-rgb),.12)}.primary{border-color:var(--primary);background:var(--primary)}.primary:hover{background:var(--primarytint1color);border-color:var(--primarytint1color)}.soft:hover,.report-controls button:hover{color:var(--primary);border-color:rgba(var(--primary-rgb),.3);background:rgba(var(--primary-rgb),.04)}.insight i,.statement h3{color:var(--primary)}.statement-row button:hover{color:var(--primary)}
</style>
