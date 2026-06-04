<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import PageHeader from '@/components/ui/PageHeader.vue'

// Deterministic Hierarchy CTE simulation
const hierarchies = ref([
  { 
    id: 1, 
    name: 'Primary Enterprise Graph (CTE Render)', 
    nodes: ['Departments', 'Projects', 'Budgets', 'Suppliers', 'Assets'],
    strict_mode: true
  },
  { 
    id: 2, 
    name: 'Compliance Audit Trail', 
    nodes: ['Ingestion', 'Validation Gate', 'Manager Approval', 'Ledger Post'],
    strict_mode: true
  }
])

const graphForm = useForm({
  focus_node: '',
  depth: 3
})
</script>

<template>
  <div>
    <PageHeader title="Institution Knowledge Graph Engine" subtitle="Strict Relational Graph Map">
      <template #actions>
        <button class="ti-btn ti-btn-primary">
          <i class="ri-git-merge-line me-1"></i> Re-Calculate CTE Matrix
        </button>
      </template>
    </PageHeader>

    <div class="grid grid-cols-12 gap-6">
      <div v-for="graph in hierarchies" :key="graph.id" class="col-span-12 xl:col-span-6">
        <div class="box h-full border-l-4 border-l-primary">
          <div class="box-header flex items-center justify-between border-b">
            <div>
              <h5 class="box-title mb-1">{{ graph.name }}</h5>
              <span class="text-xs text-textmuted font-mono">[ STRICT NODE LINKAGES ONLY ]</span>
            </div>
            <span class="badge bg-success/10 text-success"><i class="ri-check-double-line me-1"></i> Deterministic</span>
          </div>
          <div class="box-body flex items-center p-6 bg-light/30">
            <div class="flex flex-wrap items-center gap-2">
              <template v-for="(node, idx) in graph.nodes" :key="idx">
                <div class="px-4 py-2 bg-white border border-primary/20 rounded shadow-sm font-semibold text-sm">
                  {{ node }}
                </div>
                <div v-if="idx < graph.nodes.length - 1" class="text-primary px-2">
                  <i class="ri-arrow-right-line text-lg font-bold"></i>
                </div>
              </template>
            </div>
          </div>
          <div class="box-footer bg-light/50">
            <div class="text-xs text-textmuted font-mono">
              <i class="ri-database-2-line me-1"></i> Rendered via Recursive CTE 
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

