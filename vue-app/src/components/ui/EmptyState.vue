<script setup>
defineProps({
  title: { type: String, default: 'Nothing here yet' },
  message: { type: String, default: 'Records will appear here once they are available.' },
})
</script>

<template>
  <div class="text-center py-10">
    <h3 class="font-semibold">{{ title }}</h3>
    <p class="text-textmuted mt-1">{{ message }}</p>
  </div>
</template>
