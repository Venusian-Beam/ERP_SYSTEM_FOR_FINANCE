<script setup>
const model = defineModel({ type: [String, Number], default: '' })

defineProps({
  accounts: { type: Array, default: () => [] },
})
</script>

<template>
  <select v-model="model" class="form-select">
    <option value="">Select account</option>
    <option v-for="account in accounts" :key="account.code" :value="account.code">
      {{ account.code }} - {{ account.name }}
    </option>
  </select>
</template>
