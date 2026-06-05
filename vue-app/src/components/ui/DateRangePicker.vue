<script setup>
const model = defineModel({ type: Object, default: () => ({ start: '', end: '' }) })
</script>

<template>
  <div class="flex flex-wrap gap-2">
    <input v-model="model.start" class="form-control max-w-44" type="date" placeholder="Start date" />
    <input v-model="model.end" class="form-control max-w-44" type="date" placeholder="End date" />
  </div>
</template>
