<script setup>
const model = defineModel({ type: Number, default: 0 })

defineProps({
  currency: { type: String, default: 'USD' },
})
</script>

<template>
  <label class="input-group">
    <span class="input-group-text">{{ currency }}</span>
    <input v-model.number="model" class="form-control tabular-nums" min="0" step="0.01" type="number" />
  </label>
</template>
