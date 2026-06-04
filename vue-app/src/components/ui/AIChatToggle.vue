<template>
  <button 
    class="fixed bottom-[20px] right-[20px] w-12 h-12 bg-hotpink text-white rounded-full flex items-center justify-center shadow-lg z-50 hover:bg-hotpink/90 transition-colors"
    @click="togglePopup"
    aria-label="Toggle AI Chat"
  >
    <i class="ri-brain-line text-2xl"></i>
  </button>
</template>

<script setup>
import { inject } from 'vue'

const togglePopup = inject('togglePopup')
</script>

<style scoped>
:root {
  --hotpink: #ff69b4;
}
.bg-hotpink {
  background-color: var(--hotpink);
}
.hover\:bg-hotpink\/90:hover {
  background-color: rgba(255, 105, 180, 0.9);
}
</style>