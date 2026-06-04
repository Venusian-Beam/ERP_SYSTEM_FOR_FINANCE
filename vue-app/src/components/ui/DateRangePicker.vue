<script setup>
const model = defineModel({ type: Object, default: () => ({ start: '', end: '' }) })
</script>

<template>
  <div class="flex flex-wrap gap-2">
    <input v-model="model.start" class="form-control max-w-44" type="date" />
    <input v-model="model.end" class="form-control max-w-44" type="date" />
  </div>
</template>
