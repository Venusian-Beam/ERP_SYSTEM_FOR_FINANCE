<script setup>
import MoneyDisplay from './MoneyDisplay.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'

defineProps({
  approval: { type: Object, required: true },
})
</script>

<template>
  <article class="box">
    <div class="box-body flex items-center justify-between gap-4">
      <div>
        <h3 class="font-semibold">{{ approval.title }}</h3>
        <p class="text-textmuted text-sm">{{ approval.requestedBy }}</p>
      </div>
      <div class="text-end">
        <MoneyDisplay :amount="approval.amount || 0" />
        <div class="mt-2"><StatusBadge :status="approval.status || 'Pending'" /></div>
      </div>
    </div>
  </article>
</template>
