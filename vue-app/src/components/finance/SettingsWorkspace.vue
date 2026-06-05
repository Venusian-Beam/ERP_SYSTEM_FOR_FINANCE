<script setup>
import { reactive, watch } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'

const props = defineProps({
  title: String,
  subtitle: String,
  sections: { type: Array, default: () => [] },
})
const emit = defineEmits(['save'])

const local = reactive({ sections: [] })
watch(() => props.sections, (val) => { local.sections = val }, { immediate: true })

function toggle(field) {
  field.enabled = !field.enabled
}
</script>

<template>
  <section class="settings-workspace">
    <PageHeader :title="title" :subtitle="subtitle">
      <template #actions><button class="save" @click="$emit('save')"><i class="ri-save-line"></i> Save changes</button></template>
    </PageHeader>
    <div class="settings-layout">
      <nav><button v-for="(section,index) in local.sections" :key="section.title" :class="{active:index===0}"><i :class="section.icon"></i>{{ section.title }}</button></nav>
      <main>
        <article v-for="section in local.sections" :key="section.title" class="settings-card">
          <header><div><h3>{{ section.title }}</h3><p>{{ section.description }}</p></div><i :class="section.icon"></i></header>
          <div class="field-grid">
            <label v-for="field in section.fields" :key="field.label" :class="{wide:field.wide}">
              <span>{{ field.label }}</span>
              <select v-if="field.type === 'select'" :value="field.value"><option v-for="opt in field.options || [field.value]" :key="opt" :value="opt" :selected="opt === field.value">{{ opt }}</option></select>
              <div v-else-if="field.type === 'toggle'" class="toggle-row"><span class="toggle-desc">{{ field.value }}</span><button class="toggle" :class="{on:field.enabled}" @click="toggle(field)" type="button" role="switch" :aria-checked="field.enabled"><span></span></button></div>
              <textarea v-else-if="field.type === 'textarea'" :value="field.value" rows="3"></textarea>
              <input v-else :type="field.type || 'text'" :value="field.value">
              <small v-if="field.hint">{{ field.hint }}</small>
            </label>
          </div>
        </article>
      </main>
    </div>
  </section>
</template>

<style scoped>
.settings-workspace{display:flex;flex-direction:column;gap:1rem;width:100%;max-width:1040px;margin:0 auto}.settings-layout{display:grid;grid-template-columns:190px minmax(0,760px);gap:.85rem}.settings-layout nav,.settings-card{background:var(--bg-card,#fff);border:1px solid var(--border-default,#e2e8f0);border-radius:.5rem;box-shadow:var(--shadow-card,0 1px 3px rgba(15,23,42,.06))}nav{height:max-content;padding:.4rem}nav button{display:flex;align-items:center;gap:.5rem;width:100%;padding:.55rem .6rem;border:0;border-radius:.35rem;background:transparent;color:#64748b;font-size:.7rem;text-align:left;cursor:pointer}nav button.active,nav button:hover{background:rgba(99,102,241,.08);color:#6366f1}.settings-layout main{display:flex;flex-direction:column;gap:.85rem}.settings-card header{display:flex;justify-content:space-between;padding:.8rem .9rem;border-bottom:1px solid #e2e8f0}.settings-card header h3{font-size:.8rem}.settings-card header p{font-size:.65rem;color:#94a3b8;margin:0}.settings-card header>i{color:#6366f1}.field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,280px));gap:.8rem 1rem;padding:.9rem}.field-grid label{display:flex;flex-direction:column;gap:.3rem}.field-grid label.wide{grid-column:1/-1;max-width:570px}.field-grid label>span{font-size:.68rem;font-weight:600;color:#475569}.field-grid input,.field-grid select,.field-grid textarea{width:100%;border:1px solid #e2e8f0;border-radius:.35rem;padding:.48rem .6rem;background:var(--bg-card,#fff);color:var(--text-default,#334155);font:inherit;font-size:.7rem}.field-grid small{font-size:.62rem;color:#94a3b8}.toggle-row{display:flex;justify-content:space-between;align-items:center;gap:.75rem;padding:.48rem .6rem;border:1px solid #e2e8f0;border-radius:.35rem}.toggle-row .toggle-desc{font-size:.68rem;color:#64748b;line-height:1.4}.toggle{position:relative;width:2.25rem;height:1.25rem;flex-shrink:0;border:0;border-radius:99px;background:#cbd5e1;cursor:pointer;transition:background .25s ease}.toggle.on{background:var(--primary)}.toggle span{position:absolute;top:.15rem;left:.15rem;display:block;width:.95rem;height:.95rem;border-radius:50%;background:#fff;transition:left .25s ease;box-shadow:0 1px 3px rgba(0,0,0,.2)}.toggle.on span{left:1.15rem}.toggle:focus-visible{outline:2px solid var(--primary);outline-offset:2px}.save{border:1px solid #6366f1;border-radius:.35rem;padding:.45rem .7rem;background:#6366f1;color:#fff;font-size:.7rem;font-weight:600;cursor:pointer}@media(max-width:800px){.settings-layout{grid-template-columns:1fr}.settings-layout nav{display:flex;overflow:auto}.settings-layout nav button{width:auto;white-space:nowrap}}@media(max-width:560px){.field-grid{grid-template-columns:1fr}.field-grid label.wide{grid-column:auto}}
.settings-layout nav,.settings-card{border-color:rgba(var(--primary-rgb),.12)}nav button.active,nav button:hover{color:var(--primary);background:rgba(var(--primary-rgb),.08)}.settings-card header>i{color:var(--primary)}.field-grid input:focus,.field-grid select:focus,.field-grid textarea:focus{outline:none;border-color:rgba(var(--primary-rgb),.45);box-shadow:0 0 0 2px rgba(var(--primary-rgb),.08)}.toggle.on,.save{background:var(--primary)}.save{border-color:var(--primary)}.save:hover{background:var(--primarytint1color);border-color:var(--primarytint1color)}
</style>
