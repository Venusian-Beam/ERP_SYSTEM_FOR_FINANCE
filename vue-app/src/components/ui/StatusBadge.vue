<script setup>
const props = defineProps({
  status: { type: String, required: true },
})

const statusClasses = {
  approved: 'bg-success/10 text-success',
  paid: 'bg-success/10 text-success',
  pending: 'bg-warning/10 text-warning',
  overdue: 'bg-danger/10 text-danger',
  rejected: 'bg-danger/10 text-danger',
  draft: 'bg-light text-textmuted',
}
</script>

<template>
  <span class="badge rounded-sm" :class="statusClasses[props.status.toLowerCase()] || statusClasses.draft">
    {{ status }}
  </span>
</template>
